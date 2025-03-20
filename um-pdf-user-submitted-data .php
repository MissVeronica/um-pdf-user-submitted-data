<?php
/**
 * Plugin Name:      Ultimate Member - PDF User Submitted data
 * Description:      Extension to Ultimate Member for creating PDF files with Submitted User Registration Data and an option to attach PDF file links to notification emails.
 * Version:          2.0.0
 * Requires PHP:     7.4
 * Author:           Miss Veronica
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:       https://github.com/MissVeronica
 * Plugin URI:       https://github.com/MissVeronica/um-pdf-user-submitted-data
 * Update URI:       https://github.com/MissVeronica/um-pdf-user-submitted-data
 * Text Domain:      submitted-pdf
 * Domain Path:      /languages
 * UM version:       2.10.1
 * PDF code version: HTML to PDF converter code: dompdf library version 3.1.0
 * PDF code GitHub:  https://github.com/dompdf/dompdf/wiki
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

use Dompdf\Dompdf;

class UM_Submitted_PDF {

    public $plugin_key = 'submitted_pdf_';


    function __construct( ) {

        define( 'um_pdf_submitted_path', plugin_dir_path( __FILE__ ) );
        define( 'Plugin_Textdomain_SPDF', 'submitted-pdf' );
        define( 'Plugin_Basename_SPDF', plugin_basename( __FILE__ ));

        add_filter( 'um_settings_structure',                       array( $this, 'um_settings_structure_format_form_content' ), 10, 1 );
        add_filter( 'um_email_send_message_content',               array( $this, 'um_email_send_message_content_custom' ), 10, 3 );
        add_action( 'um_registration_complete',                    array( $this, 'um_registration_complete_create_pdf' ), 10, 3 );
        add_filter( 'um_before_user_submitted_registration_data',  array( $this, 'um_before_user_submitted_registration_custom' ), 10, 3 );
        add_action( 'plugins_loaded',                              array( $this, 'um_submitted_pdf_plugin_loaded' ), 0 );
        add_filter( 'plugin_action_links_' . Plugin_Basename_SPDF, array( $this, 'submitted_pdf_settings_link' ), 10 );

        add_shortcode( 'show_submitted_pdf',                       array( $this, 'show_submitted_pdf_shortcode' ));
    }

    public function um_submitted_pdf_plugin_loaded() {

        $locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
        load_textdomain( Plugin_Textdomain_SPDF, WP_LANG_DIR . '/plugins/' . Plugin_Textdomain_SPDF . '-' . $locale . '.mo' );
        load_plugin_textdomain( Plugin_Textdomain_SPDF, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function submitted_pdf_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&tab=extensions&section=submitted-pdf';
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function um_email_send_message_content_custom( $message, $slug, $args ) {

        if ( UM()->options()->get( $this->plugin_key . 'main' ) == 1 ) {

            if ( strpos( $message, '{pdf_submitted_link}' ) > 0 ) {

                $file = get_user_meta( um_user( 'ID' ), 'um_pdf_submitted', true );

                if ( file_exists( $this->get_um_filesystem( 'base_dir' ) . um_user( 'ID' ) . DIRECTORY_SEPARATOR . $file )) {

                    $pdf_url = $this->get_um_filesystem( 'base_url' ) . um_user( 'ID' ) . '/' . $file;
                    $link_text = ( ! empty( UM()->options()->get( $this->plugin_key . 'link_text' ) )) ? UM()->options()->get( $this->plugin_key . 'link_text' ) : esc_html__( 'PDF file', 'submitted-pdf' );
                    $link = '<a href="' . esc_url( $pdf_url ) . '" target="_blank">' . esc_attr( $link_text ) . '</a>';

                    $message = str_replace( '{pdf_submitted_link}', $link, $message );
                }
            }
        }

        return $message;
    }

    public function um_registration_complete_create_pdf( $user_id, $args, $form_data ) {

        if ( UM()->options()->get( $this->plugin_key . 'main' ) == 1 ) {

            $this->um_user_submitted_registration_formatted_custom();
        }
    }

    public function um_user_submitted_registration_formatted_custom() {

        require_once( um_pdf_submitted_path . "dompdf_3.1.0/autoload.inc.php" );

        $css = file_get_contents( um_path . 'assets/css/admin/modal.css' );

        $html  = '<!DOCTYPE html><html><head>';
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        $html .= '<style type="text/css">' . $css . '</style>';

        if ( UM()->options()->get( $this->plugin_key . 'external_font' ) == 1 ) {

            $tmp = sys_get_temp_dir();

            $dompdf = new Dompdf( array(
                                        'logOutputFile'   => '',
                                        'isRemoteEnabled' => true,
                                        'fontDir'         => $tmp,
                                        'fontCache'       => $tmp,
                                        'tempDir'         => $tmp,
                                        'chroot'          => $tmp ));

            $options = $dompdf->getOptions();
            $options->setDefaultFont( UM()->options()->get( $this->plugin_key . 'external_font_family' ) );
            $dompdf->setOptions( $options );

            $html .= '<link href="' . UM()->options()->get( $this->plugin_key . 'external_font_url' ) . '" rel="stylesheet" />';
            $html .= '<style>.um-admin-infobox{font-family:' . UM()->options()->get( $this->plugin_key . 'external_font_family' ) . ' !important;font-size:' . UM()->options()->get( $this->plugin_key . 'font_size' ) . '}</style>';
            $font_family = UM()->options()->get( $this->plugin_key . 'external_font_family' );

        } else {

            $dompdf = new Dompdf();

            $options = $dompdf->getOptions();
            $options->setDefaultFont( UM()->options()->get( $this->plugin_key . 'font_family' ) );
            $dompdf->setOptions( $options );

            $font_family = UM()->options()->get( $this->plugin_key . 'font_family' );

            $html .= '<style>.um-admin-infobox{font-family:' . UM()->options()->get( $this->plugin_key . 'font_family' ) . ' !important;font-size:' . UM()->options()->get( $this->plugin_key . 'font_size' ) . '}</style>';
        }

        $html .= '<style>@page {margin: 100px 50px;} #header {position: fixed;left: 0px; top: -100px; right: 0px; height: 100px;font-family:' . $font_family . ';}</style>';
        $html .= '<style>@page {margin: 100px 50px;} #footer {position: fixed;left: 0px; bottom: -50px; right: 0px; height: 70px;font-family:' . $font_family . ';}</style>';

        $html .= "</head><body>";

        if ( ! empty( UM()->options()->get( $this->plugin_key . 'header' ))) {
            $html .= '<div id="header">' . wp_kses_post( UM()->options()->get( $this->plugin_key . 'header' )) . '</div>';
        }

        if ( ! empty( UM()->options()->get( $this->plugin_key . 'footer' ))) {
            $html .= '<div id="footer">' . wp_kses_post( UM()->options()->get( $this->plugin_key . 'footer' )) . '</div>';
        }

        if ( ! empty( UM()->options()->get( $this->plugin_key . 'pre_comment' ))) {
            $html .= '<div id="comment">' . wp_kses_post( UM()->options()->get( $this->plugin_key . 'pre_comment' )) . '</div>';
        }

        $user_submitted = um_user_submitted_registration_formatted( true );
        if ( UM()->options()->get( $this->plugin_key . 'compress' ) == 1 ) {
            $user_submitted = str_replace( array( '<p>', '</p>' ), array( '<div>', '</div>' ), $user_submitted );
        }

        $html .= wp_kses_post( $user_submitted );

        if ( ! empty( UM()->options()->get( $this->plugin_key . 'post_comment' ))) {
            $html .= '<div id="comment">' . wp_kses_post( UM()->options()->get( $this->plugin_key . 'post_comment' )) . '</div>';
        }

        $html .= '</body></html>';

        if ( UM()->options()->get( $this->plugin_key . 'page_a4' ) == 1 ) {
            $dompdf->setPaper( 'A4', 'portrait' );
        }

        $dompdf->load_html( $html );
        $dompdf->render();

        if ( UM()->options()->get( $this->plugin_key . 'page_numbers' ) == 1 ) {

            $canvas = $dompdf->getCanvas();
            $canvas->page_script( function ( $pageNumber, $pageCount, $canvas, $fontMetrics ) {
                $text = sprintf( esc_html__( "Page %d of %d", 'submitted-pdf' ), $pageNumber, $pageCount );
                $font = $fontMetrics->getFont( 'monospace' );
                $pageWidth = $canvas->get_width();
                $pageHeight = $canvas->get_height();
                $size = 12;
                $width = $fontMetrics->getTextWidth( $text, $font, $size );
                $canvas->text( $pageWidth - $width - 30, $pageHeight - 30, $text, $font, $size );
            });
        }

        $output = $dompdf->output();

        $old_file = um_user( 'um_pdf_submitted' );

        if ( ! empty( $old_file )) {
            $old_file = $this->get_um_filesystem( 'base_dir' ) . um_user( 'ID' ) . DIRECTORY_SEPARATOR . $old_file;
            if ( file_exists( $old_file )) {
                unlink( $old_file );
                update_user_meta( um_user( 'ID' ), 'um_pdf_submitted', '' );
            }
        }

        $user_directory = $this->get_um_filesystem( 'base_dir' ) . um_user( 'ID' );

        if ( ! file_exists( $user_directory )) {
            wp_mkdir_p( $user_directory );
        }

        $hashed = hash( 'ripemd160', time() . mt_rand( 10, 1000 ) );
        $file = 'file_submitted_' . $hashed . '.pdf';
        $status = file_put_contents( $user_directory . DIRECTORY_SEPARATOR . $file, $output );

        if ( $status ) {
            update_user_meta( um_user( 'ID' ), 'um_pdf_submitted', $file );
        }

        return $file;
    }

    public function um_before_user_submitted_registration_custom( $user_info, $output, $submitted_data ) {

        if ( UM()->options()->get( $this->plugin_key . 'main' ) == 1 ) {

            $user_id = um_user( 'ID' );
            if ( ! empty( $user_id )) {

                $user_info = "<p><label>User ID: </label><span>$user_id</span></p>";

                $role = UM()->roles()->get_priority_user_role( $user_id );
                $role_name = $role ? wp_roles()->get_names()[ $role ] : '';

                $user_info .= "<p><label>User Role: </label><span>$role_name</span></p>";
            }
        }

        return $user_info;
    }

    public function get_um_filesystem( $function ) {

        if ( method_exists( UM()->common()->filesystem(), 'get_basedir' ) ) {

            switch( $function ) {
                case 'base_dir': $value = UM()->common()->filesystem()->get_basedir(); break;
                case 'base_url': $value = UM()->common()->filesystem()->get_baseurl(); break;
            }

        } else {

            switch( $function ) {
                case 'base_dir': $value = UM()->uploader()->get_upload_base_dir(); break;
                case 'base_url': $value = UM()->uploader()->get_upload_base_url(); break;
            }
        }

        return $value;
    }

    public function um_settings_structure_format_form_content( $settings ) {

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'um_options' ) {
            if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'extensions' ) {

                $settings['extensions']['sections']['submitted-pdf']['title'] = __( 'Submitted PDF', 'submitted-pdf' );

                if ( ! isset( $_REQUEST['section'] ) || $_REQUEST['section'] == 'submitted-pdf' ) {

                    if ( ! isset( $settings['extensions']['sections']['submitted-pdf']['fields'] ) ) {

                        $plugin_data = get_plugin_data( __FILE__ );

                        $link = sprintf( '<a href="%s" target="_blank" title="%s">%s</a>',
                                                    esc_url( $plugin_data['PluginURI'] ),
                                                    esc_html__( 'GitHub plugin documentation and download', 'submitted-pdf' ),
                                                    esc_html__( 'Plugin', 'submitted-pdf' )
                                        );

                        $header = array(
                                            'title'       => esc_html__( 'Submitted PDF', 'submitted-pdf' ),
                                            'description' => sprintf( esc_html__( '%s version %s - tested with UM 2.10.1', 'submitted-pdf' ),
                                                                                   $link, esc_attr( $plugin_data['Version'] )),
                                        );

                        $settings['extensions']['sections']['submitted-pdf'] = $header;
                        $settings['extensions']['sections']['submitted-pdf']['fields'] = $this->create_plugin_settings_fields();
                    }
                }
            }
        }

        return $settings;
    }

    public function create_plugin_settings_fields() {

        $prefix  = '&nbsp; * &nbsp;';
        $prefix2 = '&nbsp;&nbsp; -> &nbsp;';
        $plugin_key = $this->plugin_key;
        $section_fields = array();

        $message = ( UM()->options()->get( $plugin_key . 'main' ) == 1 ) ? $this->recreate_pdf_files() : '';

        $section_fields[] = array(
                'id'             => $plugin_key . 'main',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'PDF User Submitted data Enable', 'submitted-pdf' ),
                'checkbox_label' => esc_html__( 'Enable/disable the extension for creating PDF with Submitted User Registration Data.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'recreate',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'PDF User Submitted data Recreate', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'checkbox_label' => esc_html__( 'Recreate non-existent PDF files with Submitted User Registration Data for Users.', 'submitted-pdf' ),
                'description'    => esc_html( $message ),
            );

        $section_fields[] = array(
                'id'           => $plugin_key . 'slice',
                'type'         => 'select',
                'size'         => 'small',
                'label'        => $prefix2 . esc_html__( 'PDF file recreation number of Users', 'submitted-pdf' ),
                'description'  => esc_html__( 'Select number of Users in each batch of PDF file creations.', 'submitted-pdf' ) . '<br />' . 
                                  esc_html__( 'Find the seach/create result at the checkbox description line above.', 'submitted-pdf' ),
                'conditional'  => array( $plugin_key . 'recreate', '=', 1 ),
                'options'      => array(
                                        0      => 'Find total without PDF',
                                        1      => '1',
                                        10     => '10',
                                        50     => '50',
                                        100    => '100',
                                        200    => '200',
                                        300    => '300',
                                        400    => '400',
                                        500    => '500',
                                        1000   => '1000',
                                        2000   => '2000',
                                        3000   => '3000',
                                        4000   => '4000',
                                        5000   => '5000',
                                        10000  => '10000',
                                        99999  => 'No limit',
                                    ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'admin_email',
                'type'           => 'checkbox',
                'label'          => $prefix2 . esc_html__( 'Admin email enable', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'recreate', '=', 1 ),
                'checkbox_label' => esc_html__( 'Send an email to site admin with batch PDF creation summary.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'header',
                'type'           => 'textarea',
                'label'          => $prefix . esc_html__( 'PDF Header text', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'description'    => esc_html__( 'The header text may include HTML allowed for WP posts. Example: <h2>Submitted at registration</h2>', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'footer',
                'type'           => 'textarea',
                'label'          => $prefix . esc_html__( 'PDF Footer text', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'description'    => esc_html__( 'The footer text may include HTML allowed for WP posts.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'pre_comment',
                'type'           => 'textarea',
                'label'          => $prefix . esc_html__( 'PDF pre text comment', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'description'    => esc_html__( 'The comment text before the submitted fields may include HTML allowed for WP posts.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'post_comment',
                'type'           => 'textarea',
                'label'          => $prefix . esc_html__( 'PDF post text comment', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'description'    => esc_html__( 'The comment text after the submitted fields may include HTML allowed for WP posts.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'compress',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'Compress submitted text', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'checkbox_label' => esc_html__( 'Enable compressed mode ie remove extra blank lines between fields in the PDF text.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'page_numbers',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'Include Page Numbers', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'checkbox_label' => esc_html__( 'Enable "Page Number of Number of Pages" at the bottom right corner of each PDF page.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'page_a4',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'Page in "A4" format', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'checkbox_label' => esc_html__( 'Enable Page in "A4" format instead of the default North America standard "letter" format.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'link_text',
                'type'           => 'text',
                'label'          => $prefix . esc_html__( 'URL link text for the email placeholder', 'submitted-pdf' ),
                'size'           => 'small',
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'description'    => esc_html__( 'Link text for: {pdf_submitted_link}, default if field is empty; "PDF file"', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'external_font',
                'type'           => 'checkbox',
                'label'          => $prefix . esc_html__( 'PDF external font Enable', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'checkbox_label' => esc_html__( 'Enable/disable external fonts for PDF.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'external_font_url',
                'type'           => 'text',
                'label'          => $prefix2 . esc_html__( 'PDF External font URL', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'external_font', '=', 1 ),
                'description'    => esc_html__( 'Enter external URL address like Google fonts.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'external_font_family',
                'type'           => 'text',
                'label'          => $prefix2 . esc_html__( 'PDF External font family', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'external_font', '=', 1 ),
                'description'    => esc_html__( 'The font family to use from the external URL.', 'submitted-pdf' ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'font_family',
                'type'           => 'select',
                'size'           => 'small',
                'label'          => $prefix . esc_html__( 'PDF reader core font family', 'submitted-pdf' ),
                'description'    => esc_html__( 'Select one of: Courier, DejaVu Sans, DejaVu Serif, DejaVu Sans Mono, Helvetica, Times.', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'options'        => array(
                                        'Courier'           => esc_html__( 'Courier',          'submitted-pdf' ),
                                        'DejaVu Sans'       => esc_html__( 'DejaVu Sans',      'submitted-pdf' ),
                                        'DejaVu Serif'      => esc_html__( 'DejaVu Serif',     'submitted-pdf' ),
                                        'DejaVu Sans Mono'  => esc_html__( 'DejaVu Sans Mono', 'submitted-pdf' ),
                                        'Helvetica'         => esc_html__( 'Helvetica',        'submitted-pdf' ),
                                        'Times'             => esc_html__( 'Times',            'submitted-pdf' ),
                                    ),
            );

        $section_fields[] = array(
                'id'             => $plugin_key . 'font_size',
                'type'           => 'select',
                'size'           => 'small',
                'label'          => $prefix . esc_html__( 'PDF reader core or external font size', 'submitted-pdf' ),
                'description'    => esc_html__( 'Use this font size regardless of font family select from 6px to 26px.', 'submitted-pdf' ),
                'conditional'    => array( $plugin_key . 'main', '=', 1 ),
                'options'        => array(
                                        '6px'  => '6px',
                                        '8px'  => '8px',
                                        '10px' => '10px',
                                        '12px' => '12px',
                                        '14px' => '14px',
                                        '16px' => '16px',
                                        '18px' => '18px',
                                        '20px' => '20px',
                                        '22px' => '22px',
                                        '24px' => '24px',
                                        '26px' => '26px',
                                    ),
            );

        return $section_fields;
    }

    public function recreate_pdf_files() {

        $message = '';
        if ( UM()->options()->get( $this->plugin_key . 'recreate' ) == 1 && UM()->options()->get( $this->plugin_key . 'slice' ) !== null ) {

            $message = esc_html__( 'No PDF files were searched or created, you must click the "Save Changes" button.', 'submitted-pdf' );

            if ( isset( $_REQUEST['update'] ) && $_REQUEST['update'] == 'um_settings_updated' ) {

                $args = array(
                                'meta_query' => array(
                                        'relation' => 'AND',
                                        array(
                                                'key'     => 'um_pdf_submitted',
                                                'compare' => 'NOT EXISTS',
                                            ),
                                    ),
                                    'fields'  => array( 'ID' ),
                                    'orderby' => 'ID',
                                    'order'   => 'ASC',
                            );

                $users_recreate = new WP_User_Query( $args );

                if ( UM()->options()->get( $this->plugin_key . 'slice' ) == '0' ) {

                    $message = sprintf( esc_html__( 'The search found %d Users without a submitted PDF file' ), $users_recreate->total_users );
                    return $message;
                }

                if ( $users_recreate->total_users > 0 ) {

                    $users = $users_recreate->get_results();

                    $status = array( 'failures' => array(), 'empty' => array(), 'fixed' => array(), 'updated' => array() );

                    foreach( $users as $user ) {

                        $user_id = intval( $user->ID );
                        $submitted = get_user_meta( $user_id, 'submitted', true );

                        if ( ! empty( $submitted )) {

                            if ( ! is_array( $submitted )) {

                                $submitted = maybe_unserialize( $submitted );
                                if ( is_array( $submitted )) {

                                    update_user_meta( $user_id, 'submitted', $submitted );
                                    $status['fixed'][] = $user_id;

                                    UM()->user()->remove_cache( $user_id );
                                }
                            }

                            um_fetch_user( $user_id );
                            $submitted = um_user( 'submitted' );

                            if ( is_array( $submitted ) && ! empty( $submitted )) {

                                $file = $this->um_user_submitted_registration_formatted_custom();
                                $status['updated'][] = $user_id;

                                if ( UM()->options()->get( $this->plugin_key . 'slice' ) != '99999' ) {
                                    if ( intval( UM()->options()->get( $this->plugin_key . 'slice' ) ) <= count( $status['updated'] )) {
                                        break;
                                    }
                                }

                            } else {

                                $status['failures'][] = $user_id;
                            }

                            UM()->user()->remove_cache( $user_id );

                        } else {

                            $status['empty'][] = $user_id;
                        }
                    }

                    $remains = $users_recreate->total_users - count( $status['updated'] ) - count( $status['empty'] ) - count( $status['failures'] );
                    $message = sprintf( esc_html__( '%d Users without submitted PDF files got PDF created and total remaining Users are now %d, Found empty %d - error %d - fixed %d', 'submitted-pdf' ), 
                                                count( $status['updated'] ), $remains, count( $status['empty'] ), count( $status['failures'] ), count( $status['fixed'] ) );

                    if ( UM()->options()->get( $this->plugin_key . 'admin_email' ) == 1 ) {

                        $subject = wp_kses( sprintf( esc_html__( "Status PDF file creation at %s %s", 'submitted-pdf' ),
                                                                    get_bloginfo( 'name' ),
                                                                    date_i18n( 'Y/m/d', current_time( 'timestamp' )
                                                                )
                                                    ),
                                            UM()->get_allowed_html( 'templates' ),
                                        );

                        $body = '<h3>' . $subject . '</h3>
                                 <div>
                                    <p>' . $message . '</p>
                                    <p>' . "\r\nCreated PDF files for user IDs\r\n" . implode( "\r\n", $status['updated'] ) . "\r\n" . '</p>
                                    <p>' . "\r\nFailure unknown error in submitted field for user IDs\r\n" . implode( "\r\n", $status['failures'] ) . "\r\n" . '</p>
                                    <p>' . "\r\nEmpty submitted field for user IDs\r\n" . implode( "\r\n", $status['empty'] ) . "\r\n" . '</p>
                                    <p>' . "\r\nFixed submitted field format for user IDs\r\n" . implode( "\r\n", $status['fixed'] ) . "\r\n" . '</p>
                                 </div>';

                        $mail_from      = UM()->options()->get( 'mail_from' ) ? UM()->options()->get( 'mail_from' ) : get_bloginfo( 'name' );
                        $mail_from_addr = UM()->options()->get( 'mail_from_addr' ) ? UM()->options()->get( 'mail_from_addr' ) : get_bloginfo( 'admin_email' );
                        $headers        = 'From: ' . stripslashes( $mail_from ) . ' <' . $mail_from_addr . '>' . "\r\n";
                        $headers       .= "Content-Type: text/html\r\n";

                        wp_mail( um_admin_email(), $subject, $body, $headers, array() );
                    }
                }
            }
        }

        return $message;
    }

    public function show_submitted_pdf_shortcode( $atts, $content ) {

        if ( UM()->options()->get( $this->plugin_key . 'main' ) == 1 ) {

            $file_name = get_user_meta( um_profile_id(), 'um_pdf_submitted', true );

            if ( ! empty( $file_name )) {

                if ( file_exists( $this->get_um_filesystem( 'base_dir' ) . um_profile_id() . DIRECTORY_SEPARATOR . $file_name )) {

                    $file_url = $this->get_um_filesystem( 'base_url' ) . um_profile_id() . "/" . $file_name;
                    $icon     = ( isset( $atts['icon'] ) && ! empty( $atts['icon'] )) ? '<i class="' . esc_attr( $atts['icon'] ) . '"></i> ' : '';
                    $content  = ( ! empty( $content )) ? $content : esc_html__( 'Submitted PDF', 'submitted-pdf' );

                    return $icon . '<a href="' . esc_url( $file_url ) . '" target="_blank" alt="PDF file" title="' . esc_attr( $content ) . '">' . esc_attr( $content ) . '</a>';
                }
            }
        }

        return '';
    }


}

new UM_Submitted_PDF();
