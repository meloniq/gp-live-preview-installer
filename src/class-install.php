<?php
/**
 * Install class.
 *
 * @package Meloniq\GpLivePreviewInstaller
 */

namespace Meloniq\GpLivePreviewInstaller;

use GP;
use GP_Glossary_Entry;
use GP_Project;

/**
 * Install class.
 *
 * This class handles the installation of the basic plugin setup.
 */
class Install {

	/**
	 * Project ID.
	 *
	 * @var int
	 */
	public int $project_id = 0;

	/**
	 * Translation set ID.
	 *
	 * @var int
	 */
	public int $translation_set_id = 0;

	/**
	 * Glossary ID.
	 *
	 * @var int
	 */
	public int $glossary_id = 0;

	/**
	 * Last message.
	 *
	 * @var string
	 */
	public string $last_message = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Check if already installed.
		$db_status = get_option( 'gp-live-preview-installer', '' );
		if ( 'installed' === $db_status ) {
			$this->last_message = __( 'Plugin is already installed.', 'gp-live-preview-installer' );
			return;
		}

		// Proceed with installation.
		$this->create_project();
		$this->create_translation_set();
		$this->create_glossary();
		$this->create_glossary_entries();
		$this->import_originals();

		// Mark as installed.
		update_option( 'gp-live-preview-installer', 'installed' );
	}

	/**
	 * Create project.
	 *
	 * @return void
	 */
	public function create_project() {
		$data = array(
			'name'                => 'GP Live Preview Installer',
			'slug'                => 'gp-live-preview-installer',
			'description'         => 'Helper plugin to setup site for the preview with Playground',
			'source_url_template' => 'https://github.com/meloniq/gp-live-preview-installer/blob/master/%file%#L%line%',
			'parent_project_id'   => 0,
			'active'              => 1,
		);

		$new_project = new GP_Project( $data );
		$project     = GP::$project->create_and_select( $new_project );

		if ( $project ) {
			$this->project_id = $project->id;
		}
	}

	/**
	 * Create translation set.
	 *
	 * @return void
	 */
	public function create_translation_set() {
		if ( ! $this->project_id ) {
			$this->last_message = __( 'Project not found.', 'gp-live-preview-installer' );
			return;
		}

		$data            = array(
			'project_id' => $this->project_id,
			'locale'     => 'pl',
			'name'       => 'Polish',
			'slug'       => 'default',
		);
		$translation_set = GP::$translation_set->create( $data );

		if ( $translation_set ) {
			$this->translation_set_id = $translation_set->id;
		}
	}

	/**
	 * Create glossary.
	 *
	 * @return void
	 */
	public function create_glossary() {
		if ( ! $this->translation_set_id ) {
			$this->last_message = __( 'Translation set not found.', 'gp-live-preview-installer' );
			return;
		}

		$glossary = GP::$glossary->create(
			array(
				'translation_set_id' => $this->translation_set_id,
				'description'        => 'Default glossary',
			)
		);

		if ( $glossary ) {
			$this->glossary_id = $glossary->id;
		}
	}

	/**
	 * Create glossary entries.
	 *
	 * @return void
	 */
	public function create_glossary_entries() {
		if ( ! $this->glossary_id ) {
			$this->last_message = __( 'Glossary not found.', 'gp-live-preview-installer' );
			return;
		}

		$data = array(
			'glossary_id'    => $this->glossary_id,
			'term'           => 'GlotPress',
			'translation'    => 'GlotPress',
			'part_of_speech' => 'noun',
			'comment'        => 'Odmieniamy: GlotPressa, GlotPressie...',
			'last_edited_by' => 1,
		);

		$new_glossary_entry     = new GP_Glossary_Entry( $data );
		$created_glossary_entry = GP::$glossary_entry->create_and_select( $new_glossary_entry );
	}

	/**
	 * Import originals.
	 *
	 * @return void
	 */
	public function import_originals() {
		if ( ! $this->project_id ) {
			$this->last_message = __( 'Project not found.', 'gp-live-preview-installer' );
			return;
		}

		// File in parent directory - main plugin folder.
		$file_name = 'gp-live-preview-installer.pot';
		$file_path = dirname( __DIR__ ) . '/' . $file_name;

		$format = gp_get_import_file_format( 'po', $file_name );
		if ( ! $format ) {
			$this->last_message = __( 'Invalid file format.', 'gp-live-preview-installer' );
			return;
		}

		$translations = $format->read_originals_from_file( $file_path );
		if ( ! $translations ) {
			$this->last_message = __( 'No valid translations found in the uploaded file.', 'gp-live-preview-installer' );
			return;
		}

		list( $originals_added, $originals_existing, $originals_fuzzied, $originals_obsoleted, $originals_error ) = GP::$original->import_for_project( $project, $translations );

		$notice = sprintf(
			/* translators: 1: Added strings count. 2: Updated strings count. 3: Fuzzied strings count. 4: Obsoleted strings count. */
			__( '%1$s new strings added, %2$s updated, %3$s fuzzied, and %4$s obsoleted.', 'gp-live-preview-installer' ),
			$originals_added,
			$originals_existing,
			$originals_fuzzied,
			$originals_obsoleted
		);

		$this->last_message = $notice;
	}
}
