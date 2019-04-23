<?php


/**
 * Event
 */
function create_events()
{
    register_post_type('ji_event',
        array(
            'labels' => array(
                'name' => __('Events', 'jesusimage_2019'),
                'singular_name' => __('Event', 'jesusimage_2019'),
                'add_new' => __('Add an event', 'jesusimage_2019'),
                'all_items' => __('All events', 'jesusimage_2019'),
                'add_new_item' => __('Add New Event', 'jesusimage_2019'),
                'edit_item' => __('Edit Event', 'jesusimage_2019'),
            ),
            'public' => true,
            'can_export' => true,
            '_builtin' => false,
            'has_archive' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'events',
                'with_front' => false
            ),
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => false,
            'menu_icon' => 'dashicons-calendar-alt',
            'taxonomies' => array('ji_eventcategory'),
            'exclude_from_search' => false,
        )
    );
}

add_action('init', 'create_events');


function create_eventcategory_taxonomy()
{

    $labels = array(
        'name' => _x('Event categories', 'taxonomy general name', 'jesusimage_2019'),
        'singular_name' => _x('Event category', 'taxonomy singular name', 'jesusimage_2019'),
        'search_items' => __('Search Categories', 'jesusimage_2019'),
        'popular_items' => __('Popular Categories', 'jesusimage_2019'),
        'all_items' => __('All event categories', 'jesusimage_2019'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Category', 'jesusimage_2019'),
        'update_item' => __('Update Category', 'jesusimage_2019'),
        'add_new_item' => __('Add New Category', 'jesusimage_2019'),
        'new_item_name' => __('New Category Name', 'jesusimage_2019'),
        'separate_items_with_commas' => __('Separate categories with commas', 'jesusimage_2019'),
        'add_or_remove_items' => __('Add or remove categories', 'jesusimage_2019'),
        'choose_from_most_used' => __('Choose from the most used categories', 'jesusimage_2019'),
    );

    register_taxonomy('ji_eventcategory', 'ji_event', array(
        'label' => __('Event Category', 'jesusimage_2019'),
        'labels' => $labels,
        'hierarchical' => true,
        'description' => null,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'event-category'),
    ));
}

add_action('init', 'create_eventcategory_taxonomy', 0);


/**
 * Update Event
 *
 * @param $post_id
 * @return mixed
 */
function update_event($post_id)
{


    $post_type = get_post_type($post_id);


    if (($post_type != "ji_event") || (empty($_POST))) {
        return $post_id;
    }

    $cat = get_the_terms($post_id, 'ji_eventcategory')[0];

    $event_title = get_field('event_title', $post_id);


    if ($event_title == null) {
        $title = $cat->name;
    } else {
        $title = $event_title;
    }


    $my_post = array(
        'ID' => $post_id,
        'post_title' => $title,
        'post_name' => $post_id
    );


    // unhook this function so it doesn't loop infinitely
    remove_action('save_post', 'update_event');

    update_dates($post_id);

    // update the post, which calls save_post again
    wp_update_post($my_post);

    //update_field('weekend_show', $weekend_show, $post_id);

    //update_field('events_show', $events_show, $post_id);

    // re-hook this function
    add_action('save_post', 'update_event');


}

add_action('save_post', 'update_event');


/**
 * @param $false
 * @param $post_type
 * @return bool
 */
function custom_disable_months_dropdown($false, $post_type)
{

    $disable_months_dropdown = $false;

    $disable_post_types = array('ji_event');

    if (in_array($post_type, $disable_post_types)) {

        $disable_months_dropdown = true;

    }

    return $disable_months_dropdown;

}

add_filter('disable_months_dropdown', 'custom_disable_months_dropdown', 10, 2);

/**
 * Event column
 *
 * @param $columns
 *
 * @return array
 */
function ji_event_column($columns)
{

    $columns = array(
        'cb' => '<input type="checkbox" />',
        //'title'      => 'Title',
        'event_title' => 'Title',
        'event_date' => 'Date',
        'category' => 'Category',
    );

    return $columns;
}

add_filter('manage_edit-ji_event_columns', 'ji_event_column');


/**
 * Event column content
 *
 * @param $column
 * @throws Exception
 */
function ji_event_custom_column($column)
{
    global $post;

    $curr_cat = get_query_var("ji_eventcategory");

    $start = get_field('start_date', $post);
    $end = get_field('end_date', $post);

    $start_o = new DateTime($start);
    $end_o = new DateTime($end);

    $start_t = $start_o->getTimestamp();
    $end_t = $end_o->getTimestamp();

    if ($column == "event_title") {

        $id = $post->ID;

        $txt = get_the_title();

        echo "<a class='row-title' href='/wp-admin/post.php?post=$id&action=edit'>$txt</a>";

    } elseif ($column == 'event_date') {

        echo complex_date($start, $end);

        //echo date_i18n( get_option( 'date_format' ), strtotime( get_field( 'start_date', $post ) ) );

    } elseif ($column == 'category') {


        if (has_term('', 'ji_eventcategory', $post)) {

            foreach (get_the_terms($post, 'ji_eventcategory') as $cat) {

                $name = $cat->name;
                $slug = $cat->slug;
                $class = "";

                if ($curr_cat == $slug) {
                    $class = 'current';
                }

                echo "<a class='$class' href='edit.php?post_type=ji_event&ji_eventcategory=$slug'>$name</a>";


            }
        }

    }
}

add_action("manage_posts_custom_column", "ji_event_custom_column");


/**
 * @param $views
 *
 * @return mixed
 */
function ji_event_views($views)
{


    unset($views['publish']);
    unset($views['draft']);
    unset($views['trash']);
    unset($views['pending']);
    unset($views['all']);


    $post_status = 'all';

    $category = '';

    if (isset($_GET['post_status'])) {
        $post_status = $_GET['post_status'];
        $post_timing = 'all';
    } else {
        $post_timing = 'future';
    }

    if (isset($_GET['post_timing'])) {
        $post_timing = $_GET['post_timing'];
    }

    if (isset($_GET['ji_eventcategory'])) {
        $category = $_GET['ji_eventcategory'];
    }


    $tabs = array(
        array(
            'timing' => 'future',
            'name' => __('Next events')
        ),
        array(
            'timing' => 'past',
            'name' => __('Previous events')
        )
    );


    foreach ($tabs as $tab) {

        $timing = $tab['timing'];
        $name = $tab['name'];

        if ($post_timing == $timing) {
            $class = 'current';
        } else {
            $class = "";
        }

        if ($post_status == '' && $timing == 'future' && $post_timing == '') {
            $class = 'current';
        }

        $views[$timing] = "<a class='$class' href='edit.php?post_type=ji_event&ji_eventcategory=$category&post_timing=$timing'>$name</a>";

    }

    $statuses = array(
        array(
            'slug' => 'post_draft',
            'name' => __('Drafts'),
            'status' => 'draft'
        ),
        array(
            'slug' => 'post_trash',
            'name' => __('Trash'),
            'status' => 'trash'
        ),
    );

    foreach ($statuses as $status) {

        $slug = $status['slug'];
        $name = $status['name'];
        $status_name = $status['status'];

        if ($status_name == $post_status) {
            $class = 'current';
        } else {
            $class = '';
        }

        $views[$slug] = "<a class='$class' href='edit.php?post_type=ji_event&ji_eventcategory=$category&post_status=$status_name'>$name</a>";

    }

    return $views;

}

add_filter('views_edit-ji_event', 'ji_event_views');


/**
 *
 */
function ji_filter_events()
{
    $screen = get_current_screen();
    global $wp_query;
    if ($screen->post_type == 'ji_event') {
        wp_dropdown_categories(array(
            'show_option_all' => 'Show All Categories',
            'taxonomy' => 'ji_eventcategory',
            'name' => 'ji_eventcategory',
            'orderby' => 'name',
            'selected' => (isset($wp_query->query['ji_eventcategory']) ? $wp_query->query['ji_eventcategory'] : ''),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ));
    }
}

add_action('restrict_manage_posts', 'ji_filter_events');


/**
 * @param $query
 */
function perform_filtering($query)
{
    $qv = &$query->query_vars;

    if (isset($qv['ji_eventcategory'])) {
        if (($qv['ji_eventcategory']) && is_numeric($qv['ji_eventcategory'])) {
            $term = get_term_by('id', $qv['ji_eventcategory'], 'ji_eventcategory');
            $qv['ji_eventcategory'] = $term->slug;
        }
    }
}

add_filter('parse_query', 'perform_filtering');


/**
 * Order Event
 *
 * @param $query
 *
 * @return mixed
 */
function ji_order_events($query)
{


    if (!isset($query->query_vars['post_type'])) {
        return;
    }


    if ($query->query_vars['post_type'] != 'ji_event') {
        return;
    }

    $post_status = 'all';

    $category = '';

    if (isset($_GET['post_status'])) {
        $post_status = $_GET['post_status'];
        $post_timing = 'all';
    } else {
        $post_timing = 'future';
    }

    if (isset($_GET['post_timing'])) {
        $post_timing = $_GET['post_timing'];
    }

    if (isset($_GET['ji_eventcategory'])) {
        $category = $_GET['ji_eventcategory'];
    }


    if (is_admin()
        && $query->is_main_query()
        && !filter_input(INPUT_GET, 'post_status')
        //&& ! filter_input( INPUT_GET, 'ji_eventcategory' )
        && ($screen = get_current_screen()) instanceof \WP_Screen
        && $post_timing == ''
    ) {

        $post_timing = 'future';

    }

    $query = order_dates($query, 'ji_event', 'ji_eventcategory', $post_status, $post_timing);

    return $query;


}

add_action('pre_get_posts', 'ji_order_events');


/**
 * @param $content
 * @param $post
 * @return mixed
 */
function default_content_event($content, $post)
{

    if ($post->post_type != 'ji_event') {
        return $content;
    }

    return $content;

}

add_filter('default_content', 'default_content_event', 10, 2);


/**
 *
 */
function ji_event_remove_custom_taxonomy()
{
    remove_meta_box('ji_eventcategorydiv', 'ji_event', 'side');

    // $custom_taxonomy_slug is the slug of your taxonomy, e.g. 'genre' )
    // $custom_post_type is the "slug" of your post type, e.g. 'movies' )
}

add_action('admin_menu', 'ji_event_remove_custom_taxonomy');


/**
 * Add Calendar Feed
 */
function add_calendar_feed()
{
    add_feed('calendar-feed', 'export_ics');
    // Only uncomment these 2 lines the first time you load this script, to update WP rewrite rules
    //global $wp_rewrite;
    //$wp_rewrite->flush_rules( true );
}

add_action('init', 'add_calendar_feed');


/**
 * export ICS
 * @throws Exception
 */
function export_ics()
{

    /*  For a better understanding of ics requirements and time formats
        please check https://gist.github.com/jakebellacera/635416   */

    $today = date('Y-m-d H:i:s');

    $events = wp_get_recent_posts(array(
        'numberposts' => 999,
        'offset' => 0,
        'orderby' => 'meta_value',
        'meta_key' => 'start',
        'order' => 'asc',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'end',
                'compare' => '>=',
                'value' => $today,
            )
        ),
        'post_type' => 'ji_event',
        'suppress_filters' => true

    ), OBJECT);

    if ($events != null) :

        // Escapes a string of characters
        function escapeString($string)
        {
            return preg_replace('/([\,;])/', '\\\$1', $string);
        }

        // Cut it
        function shorter_version($string, $lenght)
        {
            if (strlen($string) >= $lenght) {
                return substr($string, 0, $lenght);
            } else {
                return $string;
            }
        }

        foreach ($events as $event) :

            /*  The correct date format, for ALL dates is date_i18n('Ymd\THis\Z',time(), true)
                So if your date is not in this format, use that function    */

            $timestamp = date_i18n('Ymd\THis\Z', time(), true);
            $uid = $event->ID;

            $start = get_field('start', $event);
            $end = get_field('end', $event);

            $start = new DateTime($start);
            $end = new DateTime($end);

            $start_t = $start->getTimestamp();
            $end_t = $end->getTimestamp();

            $created_date = date_i18n("Ymd\THis\Z", $start_t);
            $start_date = date_i18n("Ymd\THis\Z", $start_t);
            $end_date = date_i18n("Ymd\THis\Z", $end_t);
            $deadline = date_i18n("Ymd\THis\Z", $start_t);
            $organiser = 'Jesus Image';

            $address = get_field_or_parent('location', $event, 'ji_eventcategory');;
            $url = get_field_or_parent('event_link', $event, 'ji_eventcategory');
            $summary = get_field_or_parent('description', $event, 'ji_eventcategory');
            $content = trim(preg_replace('/\s\s+/', ' ', get_field_or_parent('description', $event, 'ji_eventcategory'))); // removes newlines and double spaces


            $title = get_the_title($event);

            //Give the iCal export a filename
            $filename = urlencode('jesusimage-ical-' . date('Y-m-d') . '.ics');
            $eol = "\r\n";

            //Collect output
            ob_start();


            // Set the correct headers for this file
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $filename);
            header('Content-type: text/calendar; charset=utf-8');
            header("Pragma: 0");
            header("Expires: 0");

            // The below ics structure MUST NOT have spaces before each line
            // Credit for the .ics structure goes to https://gist.github.com/jakebellacera/635416
            ?>
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//<?php echo get_bloginfo('name') . $eol; ?> //NONSGML Events //EN
            CALSCALE:GREGORIAN
            X-WR-CALNAME:<?php echo get_bloginfo('name') . $eol; ?>
            BEGIN:VEVENT
            CREATED:<?php echo $created_date . $eol; ?>
            UID:<?php echo $uid . $eol; ?>
            DTEND;VALUE=DATE:<?php echo $end_date . $eol; ?>
            DTSTART;VALUE=DATE:<?php echo $start_date . $eol; ?>
            DTSTAMP:<?php echo $timestamp . $eol; ?>
            LOCATION:<?php echo escapeString($address) . $eol; ?>
            DESCRIPTION:<?php echo shorter_version($content, 70) . $eol; ?>
            SUMMARY:<?php echo escapeString($title) . $eol; ?>
            ORGANIZER:<?php echo escapeString($organiser) . $eol; ?>
            URL;VALUE=URI:<?php echo escapeString($url) . $eol; ?>
            TRANSP:OPAQUE
            BEGIN:VALARM
            ACTION:DISPLAY
            TRIGGER;VALUE=DATE-TIME:<?php echo $deadline . $eol; ?>
            DESCRIPTION:Reminder for <?php echo escapeString($title);
            echo $eol; ?>
            END:VALARM
            END:VEVENT
        <?php
        endforeach;
        ?>
        END:VCALENDAR
        <?php
        //Collect output and echo
        $eventsical = ob_get_contents();
        ob_end_clean();
        echo $eventsical;
        exit();

    endif;

}

?>

