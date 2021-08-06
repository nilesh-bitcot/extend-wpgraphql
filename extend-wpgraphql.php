<?php
/**
 * Plugin Name: Extend WPGraphQL
 * Author: Plugin Author
 * Text Domain: ex-wp
 * Description: An extension to WP GraphQL plugin to show how custom data can be provide for graphql
 * 
 * Following are reference links.
 * https://www.wpgraphql.com/docs/graphql-resolvers/#gatsby-focus-wrapper
 * 
 * https://medium.com/swlh/setting-up-graphql-with-php-9baba3f21501
 * 
 * https://www.wpgraphql.com/2020/03/11/registering-graphql-fields-with-arguments/
 * 
 * https://master--wpgraphql-docs.netlify.app/extending/types/
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Check if WP GraphQL plugin is activated or not. */
register_activation_hook(__FILE__, 'ge_graphql_extension_activation');

function ge_graphql_extension_activation( $network_wide ) {
  //replace this with your dependent plugin
  if ( ! class_exists( 'WPGraphQL' ) ) {
    echo '<h5>'.__('Please install or activate WP-GraphQL plugin first. <a target="_blank" href="https://wordpress.org/plugins/wp-graphql/">download if don\'t have</a>', 'ex-wp').'</h5>';
        //Adding @ before will prevent XDebug output
        @trigger_error(__('Please install or activate WP-GraphQL plugin before activating.', 'ex-wp'), E_USER_ERROR);
  }
}


/* Default get data with api and send as result in GraphQL */

add_action( 'graphql_register_types', function() {

	register_graphql_field( 'RootQuery', 'dadJoke', [
		'type' => 'String',
		'description' => __( 'Returns a random Dad joke', 'wp-graphql' ),
		'resolve' => function() {
			$get_dad_joke = wp_remote_get('https://icanhazdadjoke.com/', [
				'headers' => [
					'Accept' => 'application/json',
					'User-Agent' => 'WPGraphQL Dad Jokes (https://github.com/wp-graphql/wp-graphql-dad-jokes)',
				],
			] );
			$body = ! empty( $get_dad_joke['body'] ) ?  json_decode( $get_dad_joke['body'] ) : null;
			$joke = ! empty( $body->joke ) ? $body->joke : null;
      // $joke = 'testinog';
			return $joke;
		},
	]);

} );

/* Set new query endpoint with arugments */

add_action( 'graphql_register_types', function() {

  $field_config = [
    'type' => 'String',
    'args' => [
      'name' => [
        'type' => 'String',
      ],
      'user_id' => [
        'type' => 'Integer'
      ],
      'score' => [
        'type' => 'Float'
      ],
      'normal_user' => [
        'type' => 'Boolean'
      ],
      'email' => [
        'type' => 'String'
      ]
    ],
    'resolve' => function( $source, $args, $context, $info ) {
      if ( isset( $args['name'] ) ) {
        return 'The value of myArg is: ' . $args['name'];
      }

      if( isset( $args['email'] ) && !empty( $args['email'] ) ) {
        $email = $args['email'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          return "$email is a valid email address";
        } else {
          graphql_debug(
            sprintf( __( 'The Value type \'%1$s\' is invalid and has not been added to the GraphQL Schema.', 'wp-graphql' ), $email ),
            [
              'type'      => 'INVALID_VALUE',
              'type_name' => 'String type email',
              'given_value' => $email
            ]
          );
          return;
        }
      }
      return 'Query test run pass.';
    },
  ];

  register_graphql_field( 'RootQuery', 'customQueryArgs', $field_config);
});


add_action( 'graphql_register_types', function() {

  $field_config = [
    'type' => [ 'list_of' => 'Cusers' ], //'Cusers',
    'args' => [
      'user_id' => [
        'type' => 'Integer',
      ]
    ],
    'resolve' => function( $source, $args, $context, $info ) {
      if ( isset( $args['user_id'] ) ) {
        // return 'The value of myArg is: ' . $args['user_id'];
        $users = get_custom_user( $args['user_id'] );
        return array($users);
      }else{
        $users = get_all_custom_user();
        return $users;
      }
      return true;
    },
  ];

  register_graphql_field( 'RootQuery', 'customTableData', $field_config);

  register_graphql_object_type( 'Cusers', [
      'description' => __( "custom users table data", 'wp-graphql' ),
      'fields' => [
        'user_id' => [
            'type' => 'Integer',
            'description' => __( 'Id of user', 'wp-graphql' ),
        ],
        'company_name' => [
            'type' => 'String',
            'description' => __( 'company of user', 'wp-graphql' ),
        ],
        'id' => [
            'type' => 'Integer',
            'description' => __( 'primary id of user', 'wp-graphql' ),
        ],
      ],
    ] );

});

/* get all users */
function get_all_custom_user() {
  global $wpdb;
  // $table = $wpdb->prefix. 'wpgraphql_custom';
  $table = 'wpgraphql_custom';
  $users = $wpdb->get_results("SELECT * from $table");
  $userResult = array();
  if( $users ){
    foreach ( $users as $key => $value ) {
      $userArr = array();
      $userArr['user_id'] = $value->user_id;
      $userArr['company_name'] = $value->company_name;
      $userArr['id'] = $value->id;
      array_push($userResult, $userArr);
    }
  }
  // print_r($userResult);
  return $userResult;
}
function get_custom_user( $user_id ) {
  global $wpdb;
  if( !$user_id ) $user_id = 1;
  $table = 'wpgraphql_custom';
  $users = $wpdb->get_results("SELECT * from $table WHERE user_id = $user_id LIMIT 1");
  $userResult = array();
  if( $users ){
    foreach ( $users as $key => $value ) {
      $userResult['user_id'] = $value->user_id;
      $userResult['company_name'] = $value->company_name;
      $userResult['id'] = $value->id;
    }
  }
  return $userResult;
}