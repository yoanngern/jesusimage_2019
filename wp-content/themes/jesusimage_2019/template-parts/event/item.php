<?php

$event = get_query_var('event');

if ($event != null) :


    $id = $event->id;
    $title = get_the_title($event);
    //$link = esc_url(get_permalink($event));
    $link = get_field('event_link', $event);
    $date = complex_date(get_field('start', $event), get_field('end', $event));
    $time = complex_time(get_field('start', $event), get_field('end', $event));

    $month = complex_month(get_field('start', $event), get_field('end', $event));
    $day = complex_day(get_field('start', $event), get_field('end', $event));

    $location = get_field('location', $event);


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

            <div class="date">
                <div class="month"><?php echo $month; ?></div>
                <div class="day"><?php echo $day; ?></div>
            </div>
            <div class="content">

                <h2><?php echo $title; ?></h2>

                <p><?php echo $text; ?></p>


                <div class="button">
                    <span><?php _e('Learn more', 'jesusimage_2019') ?></span>
                </div>
            </div>


        </a>
    </li>

<?php endif; ?>