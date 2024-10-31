<?php

/*
Plugin Name: QuizMaster Migrate
Plugin URI: http://wordpress.org/extend/plugins/quizmaster-migrate
Description: Migrate quizzes from WP Pro Quiz to QuizMaster
Version: 0.2.0
Author: Joel Milne, GoldHat Group
Author URI: https://goldhat.ca
Text Domain: quizmaster-migrate
Domain Path: /languages
*/

define('QUIZMASTER_MIGRATE_VERSION', '0.2.0');

class QuizMaster_Migrate_Plugin {

}

register_activation_hook( __FILE__, 'quizmasterMigrateActivation' );
function quizmasterMigrateActivation() {
  quizmasterTestActivation();
}

function quizmasterTestActivation() {
  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

  $qmActive = is_plugin_active('quizmaster/quizmaster.php');
	$qmProActive = is_plugin_active('quizmaster-pro/quizmaster-pro.php');

  if( !$qmActive && !$qmProActive ) {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    wp_die( __( 'QuizMaster Migrate requires QuizMaster!
      <a href="' . get_admin_url( null, 'plugins.php' ) . '">Return to Manage Plugins</a>.
      ', 'quizmaster-migrate' ) );
  }


}

// Field Definitions
define("QUIZMASTER_MIGRATE_TYPE", 'qmmg_migration_type');
define("QUIZMASTER_MIGRATE_SOURCE", 'qmmg_migration_source');
define("QUIZMASTER_MIGRATE_FILE_UPLOAD", 'qmmg_file_upload');
define("QUIZMASTER_MIGRATE_CODE_ENTRY", 'qmmg_code_entry');
define("QUIZMASTER_MIGRATE_TYPE_WPPROQUIZ", 'wpproquiz');
define("QUIZMASTER_MIGRATE_PATH", dirname(__FILE__).'/' );

// Includes
add_action('quizmaster_loaded', 'quizmasterMigrateIncludes');
function quizmasterMigrateIncludes() {
	require_once( QUIZMASTER_MIGRATE_PATH . 'lib/QuizMaster_Migrate.php');
	require_once( QUIZMASTER_MIGRATE_PATH . 'lib/QuizMaster_Quiz_Importer.php');
	require_once( QUIZMASTER_MIGRATE_PATH . 'lib/QuizMaster_Question_Importer.php');
	require_once( QUIZMASTER_MIGRATE_PATH . 'fields/fields.php');
}

// Process Migration Form
add_action('quizmaster_loaded', 'addSavePost');
function addSavePost() {
	add_action( quizmaster_get_fields_prefix() . '/save_post', 'processMigration', 20);
}

function processMigration( $post_id ) {

  $screen = get_current_screen();

  if( $screen->id != "quizmaster_page_quizmaster-migrate" ) {
    return;
  }


  $migrationType = getMigrationType();

  switch( $migrationType ) {
    case QUIZMASTER_MIGRATE_TYPE_WPPROQUIZ:
      processMigrationWpProQuiz();
      break;
  }

  resetMigrationForm();

}

function processMigrationWpProQuiz() {

  $migrationSource = getMigrationSource();

  switch( $migrationSource ) {
    case "file":
      processWpProQuizFile();
      break;
    case "code":
      processWpProQuizCode();
      break;
  }

}

function processWpProQuizCode() {

  $migrate = new QuizMaster_Migrate();
  $migrate->source = 'code';

  // migrate set data, check if valid
  $migrate->data = getMigrationCodeEntry();
  $isValid = $migrate->isValidImport();

  if( !$isValid ) {
    // not valid
    // should display errors here
    return;
  }

  // save quizzes
  $migrate->extractQuizzes();
  if( !empty( $migrate->quizzes )) {
    foreach( $migrate->quizzes as $quiz ) {

      $quiz->save();
			foreach( $quiz->questions as $question ) {
				$question->save();
				QuizMaster_Model_QuizQuestion::associate( $quiz->getId(), $question->getId() );
			}

    }
  }

}

function processWpProQuizFile() {
  $file = getMigrationFileUpload();
}

function getMigrationType() {
  return get_field( QUIZMASTER_MIGRATE_TYPE, 'option' );
}

function getMigrationSource() {
  return get_field( QUIZMASTER_MIGRATE_SOURCE, 'option' );
}

function getMigrationFileUpload() {
  return get_field( QUIZMASTER_MIGRATE_FILE_UPLOAD, 'option' );
}

function getMigrationCodeEntry() {
  return get_field( QUIZMASTER_MIGRATE_CODE_ENTRY, 'option' );
}

// Reset Migration Form
function resetMigrationForm() {

}

add_action( 'admin_menu', 'addMenu', 10 );
function addMenu() {

	$optionsPageFunc = quizmaster_get_fields_prefix() . '_add_options_page';
  $option_page = $optionsPageFunc(array(
    'page_title' 	=> 'Migration',
    'menu_title' 	=> 'Migration',
    'menu_slug' 	=> 'quizmaster-migrate',
    'parent_slug' => 'quizmaster',
    'capability' 	=> 'edit_posts',
  ));
  QuizMaster_Helper_Submenu::position( 'quizmaster-migrate', 65 );

}
