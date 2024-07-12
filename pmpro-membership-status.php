<?php
/*
Plugin Name: PMPro Membership Status
Description: A plugin to fetch user membership status using Paid Memberships Pro.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function get_user_membership_status( $data ) {
    $username = sanitize_text_field( $data['username'] );
    $user = get_user_by( 'login', $username );

    if ( ! $user ) {
        return new WP_Error( 'user_not_found', 'User not found', array( 'status' => 404 ) );
    }

    // Get the user's membership level
    $membership_level = pmpro_getMembershipLevelForUser( $user->ID );

    if ( ! $membership_level ) {
        return array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'name' => $user->display_name,
            'membership_status' => 'none',
            'membership_level' => null,
        );
    }

    return array(
        'id' => $user->ID,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'name' => $user->display_name,
        'membership_status' => 'active',
        'membership_level' => array(
            'id' => $membership_level->id,
            'name' => $membership_level->name,
            'description' => $membership_level->description,
            'confirmation' => $membership_level->confirmation,
            'billing_amount' => $membership_level->billing_amount,
            'cycle_period' => $membership_level->cycle_period,
            'cycle_number' => $membership_level->cycle_number,
            'billing_limit' => $membership_level->billing_limit,
            'trial_amount' => $membership_level->trial_amount,
            'trial_limit' => $membership_level->trial_limit,
            'allow_signups' => $membership_level->allow_signups,
        ),
    );
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom/v1', '/membership-status/', array(
        'methods' => 'GET',
        'callback' => 'get_user_membership_status',
        'args' => array(
            'username' => array(
                'required' => true,
                'validate_callback' => function( $param, $request, $key ) {
                    return is_string( $param );
                }
            ),
        ),
    ));
});
