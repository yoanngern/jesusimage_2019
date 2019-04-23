<?php

$event = get_query_var('event');

if ($event != null) :


    $id = $event->id;
    $title = get_the_title($event);
    //$link = esc_url(get_permalink($event));
    $link = get_field_or_parent('event_link', $event, 'ji_eventcategory');
    $date = complex_date(get_field('start', $event), get_field('end', $event));
    $time = complex_time(get_field('start', $event), get_field('end', $event));
    $color = get_field_or_parent('color', $event, 'ji_eventcategory');

    if ($time == '') {
        $time = get_field_or_parent('event_time', $event, 'ji_eventcategory');
    }

    $month = complex_month(get_field('start', $event), get_field('end', $event));
    $day = complex_day(get_field('start', $event), get_field('end', $event));


    $location = get_field_or_parent('location', $event, 'ji_eventcategory');


    if ($time && $location) {
        $text = $time . ' | ' . $location;
    } elseif ($time && !$location) {
        $text = $time;
    } else {
        $text = $location;
    }


    ?>


    <li class="event">
        <a href="<?php echo $link; ?>">

            <div class="date" style="border-right-color: <?php echo $color; ?>">
                <div class="month"><?php echo $month; ?></div>
                <div class="day"><?php echo $day; ?></div>
            </div>
            <div class="content">

                <h2 style="color: <?php echo $color; ?>"><?php echo $title; ?></h2>

                <p><?php echo $text; ?></p>


                <div class="button">
                    <span><?php _e('Learn more', 'jesusimage_2019') ?></span>
                </div>
            </div>


        </a>
    </li>

<?php endif; ?>