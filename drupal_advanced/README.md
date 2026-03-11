# Day 1: Work with Data (Entity & Field API, Configuration Entities)

## Password Policy Constraint

I installed the Password Policy module and its submodules using Composer and enabled them with Drush:

```bash
composer require 'drupal/password_policy:^4.0'
drush en password_policy password_policy_length password_policy_character_types password_policy_characters -y
```

I then created a policy and configured three constraints through the admin UI:

![Policy Constraints](./images/policy_constraints.png)

I implemented a custom validation constraint `PasswordPolicyConstraint` inside the `drupal_advanced` module and attached it to the `pass` field of the user entity using `hook_entity_base_field_info_alter`. The constraint validator injects the `password_policy.validator` service via `ContainerInjectionInterface` and runs the password through the active policies on every user save.

When a user registers or edits their account with a weak password, all three violations are shown:

![Password Policy Constraint result](./images/password_policy_result.png)

## What is AccessResult and how does it work?

`AccessResult` is Drupal's object-oriented way of expressing access control decisions. It has three states: `allowed()`, `forbidden()`, and `neutral()`. Unlike raw booleans, every result carries cacheability metadata so Drupal knows how to vary and invalidate the page cache correctly. Results can be combined with `andIf()` and `orIf()`, and `forbidden()` always wins over `allowed()`.

## Scaffolding a Custom Entity

Generating a custom entity by hand involves a lot of boilerplate — entity class, routing, forms, handlers, and multiple YAML files. The fastest way is using Drush with Drupal Code Generator:

```bash
drush generate entity:content
drush generate entity:configuration
```

This generates everything wired together and ready to extend.

## Getting a Field Definition via Code

```php
$fields = \Drupal::service('entity_field.manager')
  ->getFieldDefinitions('node', 'article');

$definition = $fields['title'];
$definition->getType();
$definition->getLabel();
$definition->getSettings();
```

## Multiple Formatters for a Field Type

Yes, a field type and its formatters are fully decoupled. You can register as many formatters as you want for the same field type by pointing multiple `#[FieldFormatter]` plugins at the same `field_types` value. They all appear as options in the Manage Display UI. Drupal core already does this — the `text` field type ships with Default, Plain text, and Trimmed formatters out of the box.

## Retrieving Module Config via Drush

```bash
drush config:get mymodule.settings
drush config:get mymodule.settings some_key
drush config:edit mymodule.settings
```

# Day 2: Work with Hooks

## Adding a new base field to an existing entity type using `hook_entity_base_field_info`

This hook lets you add a new field to an existing entity type you don't own. It targets the entity type level, meaning the field is added to all bundles. If you only need it on one bundle, use `hook_entity_bundle_field_info` instead.

```php
function drupal_advanced_entity_base_field_info(EntityTypeInterface $entity_type): array {
  $fields = [];

  if ($entity_type->id() === 'node') {
    $fields['drupal_advanced_subtitle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subtitle'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  return $fields;
}
```

After adding this, run `drush updb` to create the column in the database.

## What is the role of `hook_update_n`

It runs a one-time update on an already installed module. Drupal tracks which updates have run so it never runs the same one twice. Think of it like a database migration.

The number follows this structure: `[major_version][schema_version][sequential_number]`

```
hook_update_10001
  10 → Drupal 10
  0  → module schema version
  01 → first update
```

```php
/**
 * Add subtitle field to node table.
 */
function drupal_advanced_update_10001(): void {
  $field = BaseFieldDefinition::create('string')
    ->setLabel(t('Subtitle'));

  \Drupal::entityDefinitionUpdateManager()
    ->installFieldStorageDefinition(
      'drupal_advanced_subtitle',
      'node',
      'drupal_advanced',
      $field
    );
}
```

```bash
drush updb          # run all pending updates
drush updb --no     # preview without running
```

## What is the role of `hook_install`

Runs once when the module is first enabled. Used for one-time setup like creating default config, inserting initial data, or setting up default terms.

```php
function drupal_advanced_install(): void {
  \Drupal::configFactory()
    ->getEditable('drupal_advanced.settings')
    ->set('enabled', TRUE)
    ->save();
}
```

```
drush en drupal_advanced  → hook_install runs once, never again
drush updb                → hook_update_n runs for any updates Drupal hasn't seen yet
```

```
hook_install    → building the house before anyone moves in
hook_update_n   → renovating the house while people are still living in it
```

## Prefixing all newly created article nodes with `HEY-` using `hook_ENTITY_TYPE_presave`

`presave` fires right before the entity hits the database so you can modify values before they are saved. `$node->isNew()` ensures the prefix is only added on creation, not on every update.

```php
function drupal_advanced_node_presave(NodeInterface $node): void {
  if ($node->isNew() && $node->getType() === 'article') {
    $current_title = $node->getTitle();

    if (!str_starts_with($current_title, 'HEY-')) {
      $node->setTitle('HEY-' . $current_title);
    }
  }
}
```

The full entity lifecycle:

```
presave  → before DB  → can still modify entity
insert   → after DB   → new entity, cannot modify
update   → after DB   → existing entity, cannot modify
delete   → before DB  → entity being deleted
```

## What is the role of `$entity->original`

When an entity is updated, Drupal loads the previous version from the database and attaches it as `$entity->original`. This lets you compare old and new values. It is `NULL` on new entities so always check `!$entity->isNew()` first.

```php
function drupal_advanced_node_presave(NodeInterface $node): void {
  if (!$node->isNew() && $node->original) {
    $old_title = $node->original->getTitle();
    $new_title = $node->getTitle();

    if ($old_title !== $new_title) {
      \Drupal::logger('drupal_advanced')
        ->info('Title changed from @old to @new', [
          '@old' => $old_title,
          '@new' => $new_title,
        ]);
    }
  }
}
```

```
new entity    → $entity->original is NULL
update        → $entity->original has the old data
```

Available in both `presave` and `update` hooks.

## How to override a Theme Hook provided by another module

Drupal has a strict priority order:

```
active theme   → always wins
custom module  → wins over contrib
contrib module → lowest priority
```

The simplest way is to create a template with the same name in your active theme, no code needed:

```
mytheme/
  templates/
    user.html.twig   ← Drupal picks this, ignores the module's version
```

To override from a module instead of a theme, use `hook_theme_registry_alter` to point Drupal to your module's templates folder:

```php
function drupal_advanced_theme_registry_alter(array &$theme_registry): void {
  if (isset($theme_registry['user'])) {
    $theme_registry['user']['path'] = \Drupal::service('extension.list.module')
      ->getPath('drupal_advanced') . '/templates';
  }
}
```

## Adding a theme suggestion for `user` based on view mode using `hook_theme_suggestions_alter`

Theme suggestions tell Drupal to look for a more specific template before falling back to the default. This lets you have a different template per view mode without touching the original.

```php
function drupal_advanced_theme_suggestions_alter(
  array &$suggestions,
  array $variables,
  string $hook
): void {
  if ($hook === 'user') {
    $view_mode = $variables['elements']['#view_mode'] ?? 'full';
    $suggestions[] = 'user__' . $view_mode;
  }
}
```

Then create the templates in your active theme:

```
mytheme/
  templates/
    user.html.twig            ← default fallback
    user--teaser.html.twig    ← used only in teaser mode
    user--full.html.twig      ← used only in full mode
```

The naming convention:

```
suggestion: user__teaser   → filename: user--teaser.html.twig
             __ = --
```

How it flows:

```
user rendered in teaser mode
  → suggestion user__teaser added
  → Drupal looks for user--teaser.html.twig
  → found → uses it
  → not found → falls back to user.html.twig
```

This same pattern works for any use case for example switching layout based on a query string:

```
/content?type=grid    → node--grid.html.twig
/content?type=list    → node--list.html.twig
/content?type=columns → node--columns.html.twig
```

```bash
drush cr   # always clear cache after adding new templates
```
