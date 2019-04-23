<?php


function ji_events_ical()
{

    // - start collecting output -
    ob_start();

    // - file header -
    header('Content-type: text/calendar');
    header('Content-Disposition: attachment; filename="ical.ics"');

    // - content header -
    ?>
    BEGIN:VCALENDAR
    VERSION:2.0
    PRODID:-//<?php the_title(); ?>//NONSGML Events //EN
    X-WR-CALNAME:<?php the_title();
    _e(' - Events', 'jesusimage_2019'); ?>
    X-ORIGINAL-URL:<?php echo the_permalink(); ?>
    X-WR-CALDESC:<?php the_title();
    _e(' - Events', 'jesusimage_2019'); ?>
    CALSCALE:GREGORIAN

    <?php


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

    // - loop -
    if ($events != null):
        //global $post;
        foreach ($events as $event):


            $start = get_field('start', $event);
            $end = get_field('end', $event);

            $start = new DateTime($start);
            $end = new DateTime($end);

            $start_t = $start->getTimestamp();
            $end_t = $end->getTimestamp();

            //$created_date = date_i18n("Ymd\THis\Z", $start_t);
            $start_date = date_i18n("Ymd\THis\Z", $start_t);
            $end_date = date_i18n("Ymd\THis\Z", $end_t);
            //$deadline = date_i18n("Ymd\THis\Z", $start_t);

            $title = get_the_title($event);

            // - item output -
            ?>
            BEGIN:VEVENT
            DTSTART:<?php echo $start_date; ?>
            DTEND:<?php echo $end_date; ?>
            SUMMARY:<?php echo $title; ?>
            DESCRIPTION:<?php echo ''; ?>
            END:VEVENT
        <?php
        endforeach;
    endif;
    ?>
    END:VCALENDAR
    <?php
    // - full output -
    $jieventsical = ob_get_contents();
    ob_end_clean();
    echo $jieventsical;
}

function add_ji_events_ical_feed()
{
    // - add it to WP RSS feeds -
    add_feed('ji-events-ical', 'ji_events_ical');
}

add_action('init', 'add_ji_events_ical_feed');