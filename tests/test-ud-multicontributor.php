<?php
/**
 * PHPUnit test case for the MultiContributor metabox plugin.
 */

class TestUdMultiContributor extends WP_UnitTestCase {

	/**
	 * Test if the plugin class is loaded correctly.
	 *
	 * @group plugin_loaded
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( class_exists( 'UDMC\UdMultiContributor' ) );
	}

	/**
	 * Test if contributors are saved when the post is saved.
	 *
	 * @group contributors_saved
	 */
	public function test_contributors_saved() {

		UDMC\UdMultiContributor::get_instance();

		$u1 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u3 = $this->factory->user->create( array( 'role' => 'author' ) );

		// Create a user without the capability to edit posts.
		$u4 = $this->factory->user->create( array( 'role' => 'author' ) );

		// Log in as the newly created user.
		wp_set_current_user( $u4 );

		// Mock the post data.
		$_POST['udmc_nonce_field'] = wp_create_nonce( 'udmc_save_contrubutors' );
		$_POST['chk_multi_contributer'] = array(
			UDMC\udmc_crypt( $u1, 'e' ),
			UDMC\udmc_crypt( $u2, 'e' ),
			UDMC\udmc_crypt( $u3, 'e' )
		);

		// Create a new post.
		$post_id = $this->factory->post->create();

		$contributors = get_post_meta( $post_id, '_udmc_multi_contributer', true );

		// Check if authors are saved.
		$this->assertEquals( $contributors, "{$u1},{$u2},{$u3}" );
	}

	/**
	 * Test if contributors are saved without login.
	 *
	 * @group contributors_saved
	 */
	public function test_contributors_saved_without_login() {

		UDMC\UdMultiContributor::get_instance();

		$u1 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u3 = $this->factory->user->create( array( 'role' => 'author' ) );

		// Mock the post data.
		$_POST['udmc_nonce_field'] = wp_create_nonce( 'udmc_save_contrubutors' );

		// Create a new post.
		$post_id = $this->factory->post->create();

		$contributors = get_post_meta( $post_id, '_udmc_multi_contributer', true );

		// Check if authors are saved.
		$this->assertNotEquals( $contributors, "{$u1},{$u2},{$u3}" );
	}

	/**
	 * Test if contributors are saved without permission.
	 * 
	 * @group contributors_saved
	 */
	public function test_contributors_saved_without_permission() {

		UDMC\UdMultiContributor::get_instance();

		$u1 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u3 = $this->factory->user->create( array( 'role' => 'author' ) );

		// Create a user without the capability to edit posts.
		$u4 = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Log in as the newly created user.
		wp_set_current_user( $u4 );

		// Mock the post data.
		$_POST['udmc_nonce_field'] = wp_create_nonce( 'udmc_save_contrubutors' );

		// Create a new post.
		$post_id = $this->factory->post->create();

		$contributors = get_post_meta( $post_id, '_udmc_multi_contributer', true );

		// Check if authors are saved.
		$this->assertNotEquals( $contributors, "{$u1},{$u2},{$u3}" );
	}

	/**
	 * Test for post types other than 'post'.
	 *
	 * @group contributors_saved
	 */
	public function test_contributors_saved_other_post_type() {

		UDMC\UdMultiContributor::get_instance();

		$u1 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u2 = $this->factory->user->create( array( 'role' => 'author' ) );
		$u3 = $this->factory->user->create( array( 'role' => 'author' ) );

		// Create a user without the capability to edit posts.
		$u4 = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Log in as the newly created user.
		wp_set_current_user( $u4 );

		// Mock the post data.
		$_POST['udmc_nonce_field'] = wp_create_nonce( 'udmc_save_contrubutors' );

		// Create a new post of type 'page'.
		$post_id = $this->factory->post->create( array(
				'post_type' => 'page',
			)
		);

		$contributors = get_post_meta( $post_id, '_udmc_multi_contributer', true );

		// Check if authors are saved.
		$this->assertNotEquals( $contributors, "{$u1},{$u2},{$u3}" );
	}

}