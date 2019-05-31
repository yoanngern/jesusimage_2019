<?php

/**
 * Order Tuition forms
 *
 * @param $query
 *
 * @return mixed
 */
function ji_order_tuition_forms($query)
{

    if ($query->query_vars['give_forms_category'] != 'tuition') {
        return;
    }


    if (!$query->is_main_query()) {


        return $query;

    }

    // only modify queries for category
    if (isset($query->query_vars['give_forms_category'])) {
        $query->set('orderby', 'title');
        $query->set('order', 'asc');

        return $query;
    }


    return $query;


}

add_action('pre_get_posts', 'ji_order_tuition_forms');

