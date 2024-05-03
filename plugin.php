<?php
/*
Plugin Name: My Nutrition Tracker
Description: A simple nutrition planner and tracker for gym members.
Author: Lloyd Berko
*/

function mnt_daily_intake_form() {
    $content = '<form action="" method="post">' .
                '<input type="text" name="food_item" placeholder="Enter a food item">' .
                '<input type="number" name="calories" placeholder="Calories">' .
                '<input type="submit" name="mnt_submit" value="Add to tracker">' .
                '</form>';
    return $content;

}
add_shortcode('daily_intake_form', 'mnt_daily_intake_form');

function mnt_handle_form_submission() {
    if (isset($_POST['mnt_submit'])) {
        
        $food_item = $_POST['food_item'];
        $calories = intval($_POST['calories']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'nutrition_tracker';

        $wpdb->insert($table_name,
            array('food_item' => $food_item, 'calories' => $calories, 'date' => current_time('mysql')),
            array('%s','%d','%s')
        );
        if ($wpdb->insert_id>0) {
            echo '<p>Added successfully!</p>';
        } else {
            echo '<p>Error.</p>';
        }
    }
}
add_action('init', 'mnt_handle_form_submission');

function mnt_create_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition_tracker';

    $collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        food_item varchar(255) NOT NULL,
        calories int(9) NOT NULL,
        date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $collate;";

}
register_activation_hook(__FILE__, 'mnt_create_database_table');

function mnt_add_admin_menu() {
    add_menu_page(
        'Nutrition Tracker', 'Nutrition Tracker', 'manage_options', 'mnt_nutrition_tracker', 
        'mnt_nutrition_tracker_page','dashicons-carrot', 6                       
    );
}
function mnt_nutrition_tracker_page() {
    echo '<div class="wrap"><h1>Nutrition Tracker</h1>';
    echo do_shortcode('[daily_intake_form]'); 
    mnt_display_nutrition_data(); 
    echo '</div>';
}
add_action('admin_menu', 'mnt_add_admin_menu');

function mnt_display_nutrition_data() {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nutrition_tracker");
    if ($results) {
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>Food Item</th><th>Calories</th><th>Date</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $item) {
            echo "<tr><td>{$item->food_item}</td><td>{$item->calories}</td><td>{$item->date}</td>
            <td><button class='delete-btn' data-id='{$item->id}'>Delete</button></td></tr>";
        }
        echo '</tbody></table>';
    }
}
