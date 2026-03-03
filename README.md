# Custom Modules

This directory contains the custom Drupal modules used in this project.

Modules included:

- **hello_world**: A minimal example module that provides a simple route, controller, block, and an admin settings form to set a name shown in the block.
- **movie_directory**: A custom module that integrates with a movie API to display a movie listing. It includes a service `MovieApiConnector`, a controller for the listing page, an admin configuration form for the API base URL and API key, and Twig templates for the listing and movie cards.

Quick overview:

- hello_world
  - Provides a simple page and a block that displays a greeting using the configured name.
  - Files: `hello_world.info.yml`, `hello_world.routing.yml`, `hello_world.links.menu.yml`, `src/Controller/HelloController.php`, `src/Form/HelloSettingsForm.php`, `src/Plugin/Block/HelloBlock.php`.

- movie_directory
  - Integrates with an external movie API, stores API configuration via an admin form, and renders a movie listing page with a set of cards.
  - Files: `movie_directory.info.yml`, `movie_directory.routing.yml`, `movie_directory.services.yml`, `movie_directory.module`, `src/MovieApiConnector.php`, `src/Controller/MovieListingController.php`, `src/Form/MovieApi.php`, `templates/movie-listing.html.twig`, `templates/movie-card.html.twig`, `assets/css/movie-styles.css`.

# Custom Modules

This folder contains two custom Drupal modules created for demonstration and integration with an external movie API. The summaries below explain what each module does, which components they include, and how to use them.

## hello_world

- Purpose: A minimal example module that demonstrates common Drupal extension points: a route/controller, a configurable admin form, and a block plugin. It is useful as a learning reference or a quick demo.
- Key functionality:
  - Provides an admin settings form where an administrator can enter a name to be used by the module.
  - Exposes a simple page (via a controller) that can display content or links related to the module.
  - Provides a block plugin that reads the configured name and renders a greeting (for example, "Hello, ansar!").
- Main files and roles:
  - `hello_world.info.yml`: Module metadata used by Drupal.
  - `hello_world.routing.yml`: Declares any custom routes (pages) the module provides.
  - `hello_world.links.menu.yml`: Optionally adds menu links for the admin or site menus.
  - `src/Controller/HelloController.php`: Controller for the module's page routes.
  - `src/Form/HelloSettingsForm.php`: Configuration form that saves the greeting name to configuration storage.
  - `src/Plugin/Block/HelloBlock.php`: Block plugin that displays the stored name in a themed block.
- Usage:
  1.  Enable the module: `drush en hello_world -y` or use the Extend UI.
  2.  Visit Administration → Configuration → Hello World Settings to enter the name.
  3.  Place the "Hello block" in a region through the Block Layout UI or visit the module's page route.

## movie_directory

- Purpose: Integrates with an external movie API to fetch and display movies in a styled listing. Demonstrates the use of services, configuration forms, controllers, templates, and static assets.
- Key functionality:
  - Provides an admin configuration form to set the API base URL and API key.
  - Implements a service (`MovieApiConnector`) responsible for making HTTP requests to the remote movie API and returning parsed results.
  - Offers a controller (`MovieListingController`) which uses the service to fetch movies and passes data to Twig templates.
  - Contains Twig templates to render a responsive movie grid and individual movie cards.
  - Includes CSS in `assets/css/movie-styles.css` for card layout and styling.
- Main files and roles:
  - `movie_directory.info.yml`: Module metadata.
  - `movie_directory.routing.yml`: Defines the public route for the movie listing page.
  - `movie_directory.services.yml`: Registers the `MovieApiConnector` service and any other services.
  - `movie_directory.module`: Module hooks (if any) and lightweight integration code.
  - `src/MovieApiConnector.php`: Service that handles API requests and error handling.
  - `src/Controller/MovieListingController.php`: Controller that prepares data for the listing page.
  - `src/Form/MovieApi.php`: Admin form that stores `api_base_url` and `api_key` in configuration.
  - `templates/movie-listing.html.twig`: Page template for the full listing.
  - `templates/movie-card.html.twig`: Template for individual movie cards used by the listing.
  - `assets/css/movie-styles.css`: Styles for the movie grid and cards.
- Usage:
  1.  Enable the module: `drush en movie_directory -y` or use the Extend UI.
  2.  Visit Administration → Configuration → Movie API Configuration and provide your API base URL and API key.
  3.  Visit the movie listing route (as defined in `movie_directory.routing.yml`) to see results. The controller will use the configured API credentials to fetch movie data.

## Screenshots

The following screenshots illustrate the modules in action. Place the referenced images in this directory (or update the paths below) so they render on the repository page.

1. Hello World settings form
   ![Hello World Settings](./images/hello-world-settings.png)

2. Hello World page and block
   ![Hello World Page](./images/hello-world-page.png)

3. Movie listing page (grid)
   ![Movie Listing](./images/movie-listing.png)

4. Movie API Configuration form
   ![Movie API Config](./images/movie-api-config.png)

## Usage (Quick Commands)

Enable both modules with Drush:

```bash
drush en hello_world movie_directory -y
```

Configure the modules via the administration UI:

- `Administration → Configuration → Hello World Settings` — set the greeting name.
- `Administration → Configuration → Movie API Configuration` — set the API base URL and API key.

## Notes and next steps

- The screenshots referenced here are not yet added to the repository. If you want, I can add the images into this folder now — tell me to proceed and I will import them from the attachments or you can provide them.
- If you need route names, menu links, or exact config keys referenced in the code, I can extract them from the module files and add exact examples to this README.
