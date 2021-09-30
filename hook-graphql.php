<?php // phpcs:ignore

/**
 * Plugin Name:     Add WPGraphQL Subscriber
 * Plugin URI:      https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description:     This is WPGraphQL addon for adding graphql ability for subscriber
 * Author:          hieu nguyen
 * Author URI:      https://hieunguyen.dev
 * Version:         1.0.0
 *
 * @package         WP_Graphql_Subscriber
 */

add_action( 'graphql_register_types', function() {
    
    register_graphql_mutation( 'subscribe', [

        # inputFields expects an array of Fields to be used for inputting values to the mutation
        'inputFields'         => [
            'esfpx_name' => [
                'type' => 'String',
                'description' => __( 'subscriber name', 'your-textdomain' ),
            ],
            'esfpx_email' => [
                'type' => 'String',
                'description' => __( 'subscriber email', 'your-textdomain' ),
            ],
            'esfpx_es-subscribe' => [
                'type' => 'String',
                'description' => __( 'es subscribe', 'your-textdomain' ),
            ],
            'esfpx_es_email_page' => [
                'type' => 'Integer',
                'description' => __( 'email page', 'your-textdomain' ),
            ],
            'esfpx_lists' => [
                'type' => 'String',
                'description' => __( 'lists', 'your-textdomain' ),
            ],
            'esfpx_form_id' => [
                'type' => 'Integer',
                'description' => __( 'form id', 'your-textdomain' ),
            ],
            'esfpx_es_email_page_url' => [
                'type' => 'String',
                'description' => __( 'form id', 'your-textdomain' ),
            ]
        ],
    
        # outputFields expects an array of fields that can be asked for in response to the mutation
        # the resolve function is optional, but can be useful if the mutateAndPayload doesn't return an array
        # with the same key(s) as the outputFields
        'outputFields'        => [
            'subscribeId' => [
                'type' => 'Integer',
                'description' => __( 'id of the subscriber', 'your-textdomain' ),
                'resolve' => function( $payload, $args, $context, $info ) {
                               return isset( $payload['subscribed'] ) ? $payload['subscribed'] : null;
                }
            ]
        ],
    
        # mutateAndGetPayload expects a function, and the function gets passed the $input, $context, and $info
        # the function should return enough info for the outputFields to resolve with
        'mutateAndGetPayload' => function( $input, $context, $info ) {
            // Do any logic here to sanitize the input, check user capabilities, etc
            $subscribed = 0;
            $contact_id = ES()->contacts_db->get_contact_id_by_email( $input['esfpx_email'] );
            if ( !$contact_id ) {
                if ( ! empty( $input['esfpx_name'] ) ) {
					// Get First Name and Last Name from Name.
					$name_parts = ES_Common::prepare_first_name_last_name($input['esfpx_name'] );
					$first_name = $name_parts['first_name'];
					$last_name  = $name_parts['last_name'];
				}
                $list_hashes = isset( $input['esfpx_lists'] ) ? [$input['esfpx_lists']] : array();
                $list_hash_str  = ES()->lists_db->prepare_for_in_query( $list_hashes );
                $where          = "hash IN ($list_hash_str)";
                $listIds = ES()->lists_db->get_column_by_condition( 'id', $where );



                $data               = array();
                $data['first_name'] = $first_name;
                $data['last_name']  = $last_name;
                $data['source']     = 'form';
                $data['form_id']    = 1;
                $data['email']      =  $input['esfpx_email'];
                $data['hash']       = ES_Common::generate_guid();            
                $data['status']     = 'verified';                
                $data['created_at'] = ig_get_current_date_time();
                $data['updated_at'] = null;
                $data['meta']       = null;
                $contact_id = ES()->contacts_db->insert( $data );

                $list_contact_data = array(
                    'contact_id'    => $contact_id,
                    'status'        => 'Unconfirmed',
                    'subscribed_at' => ig_get_current_date_time(),
                    'optin_type'    => 2,
                    'subscribed_ip' => '',
                );

                ES()->lists_contacts_db->add_contact_to_lists( $list_contact_data,  $listIds );
            }

            return [
                'subscribeId' => $contact_id,
            ];
        }
    ] );
});