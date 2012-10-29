<?php

require 'wp-php/Site.php';
require 'wp-php/Object.php';
require 'wp-php/Post.php';
require 'wp-php/Term.php';
require 'wp-php/PostTerms.php';
require 'wp-php/Exception.php';
header("Content-Type: text/html; charset=utf-8");

// Wordpress settings (be sure to enable XML RPC publishing, under “Settings” and then “Writing”) 
$url = 'http://www.example.com/xmlrpc.php';
$user = 'admin';
$password = 'yourpassword';
$blog_id = 1; //If you aren't using Wordpress MU, keep it to 1

try {
    $wordpress = new Wordpress_Site($url, $user, $password, $blog_id);

    //List 25 post
    $posts = $wordpress->get_posts(Wordpress_Post::TYPE_POST, Wordpress_Post::STATUS_PUBLISHED, 25);

    echo '<h2>Newer posts</h2>';
    echo '<ul>';
    foreach ($posts as $post) {
        /* @var Wordpress_Post $post */
        echo "<li><a href='$post->link'>$post->post_title (ID $post->post_id)</a></li>";
    }
    echo '</ul>';

    //List categories
    $categories = $wordpress->get_terms(Wordpress_Term::TYPE_CATEGORY, 20);

    echo '<h2>Categories</h2>';
    echo '<ul>';
    foreach ($categories as $category) {
        /* @var Wordpress_Taxonomy $category */
        echo "<li><a href='$category->slug'>$category->name (ID $category->term_id)</a></li>";
    }
    echo '</ul>';

    //Create, edit and delete post
    echo '<h2>Create, edit and delete objects</h2>';

    //Create category
    $category = new Wordpress_Term($wordpress);
    $category->taxonomy = Wordpress_Term::TYPE_CATEGORY;
    $category->name = 'hello world ' . mt_rand();
    $category->save();
    echo "<p>Category created: $category->name (slug: $category->slug, id: $category->term_id)</p>";

    $post = new Wordpress_Post($wordpress);
    $post->post_type = Wordpress_Post::TYPE_POST;
    $post->post_status = Wordpress_Post::STATUS_PUBLISHED;
    $post->post_title = 'Hello world!';
    $post->post_content = 'Hello world from PHP :)';
    $post->custom_fields = array(
        array('key' => 'custom1', 'value' => 'value1'),
        array('key' => 'custom2', 'value' => 'value2')
    );
    $post->terms->add($category);
    $post->terms->add_tag('hello');
    $world_tag = $post->terms->add_tag('world');
    $post->save();
    echo "<p>Post created: <a href='$post->link'>$post->post_title (ID $post->post_id)</a></p>";

    $post->post_title = 'Hello world, again!';
    $post->post_content = 'New content edited from PHP';
    $post->terms->add_category('other category', 'Other category created from PHP');
    $post->terms->add_tag('other tag');
    $post->terms->delete($post->terms->find_by_name('world'));
    $post->save();

    echo "<p>Post edited: <a href='$post->link'>$post->post_title (ID $post->post_id)</a></p>";

    //Delete everything created
    foreach ($post->terms->all() as $term) {
        $term->delete();
    }
    $world_tag->delete();
    $post->delete();
    echo "<p>All created objects deleted</a></p>";
} catch (Wordpress_Exception $exception) {
    echo '<h2 style="color:darkred">Wordpress Error!</h2><p>', $exception, '</p>';
} catch (Exception $exception) {
    echo '<h2 style="color:darkred">Error!</h2><p>', $exception, '</p>';
}