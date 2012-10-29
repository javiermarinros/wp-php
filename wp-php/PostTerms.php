<?php

/**
 * Represents a collection of terms related with a post 
 */
class Wordpress_PostTerms {

    /**
     * Post owner of this terms
     * @var Wordpress_Post 
     */
    private $_post;
    
    /**
     *
     * @var Wordpress_Term[] 
     */
    private $_terms;
    private $_changed = FALSE;

    public function __construct(Wordpress_Post $post, array $terms_data = array()) {
        $this->_post = $post;

        $this->_terms = array();
        foreach ($terms_data as $term_data) {
            $this->_terms[] = new Wordpress_Term($post->site(), $term_data);
        }
    }

    public function changed() {
        return $this->_changed;
    }
    
    /**
     * Retrieves the list of all terms of this post
     * @return Wordpress_Term[]
     */
    public function all(){
        return $this->_terms;
    }

    /**
     * Add a taxonomy term to the post.
     * @param Wordpress_Term|Wordpress_Term::TYPE_*|string $taxonomy
     * @param string $name
     * @return Wordpress_Term
     */
    public function add($taxonomy, $name = NULL, $description = NULL, $slug = NULL) {
        if ($taxonomy instanceof Wordpress_Term) {
            $term = $taxonomy;
        } else {
            $term = new Wordpress_Term($this->_post->site());
            $term->taxonomy = $taxonomy;
            $term->name = $name;

            if (isset($description))
                $term->description = $description;

            if (isset($slug))
                $term->slug = $slug;
        }

        $this->_changed = TRUE;
        $this->_terms[] = $term;
        return $term;
    }

    public function add_category($name, $description = NULL, $slug = NULL) {
        return $this->add(Wordpress_Term::TYPE_CATEGORY, $name, $description, $slug);
    }

    public function add_tag($name, $description = NULL, $slug = NULL) {
        return $this->add(Wordpress_Term::TYPE_TAG, $name, $description, $slug);
    }

    /**
     * Find a term by name
     * @param string $name
     * @param Wordpress_Term::TYPE_*|string $taxonomy
     * @return Wordpress_Term|FALSE
     */
    public function find_by_name($name, $taxonomy = NULL) {
        foreach ($this->_terms as $post_term) {
            if (!isset($taxonomy) || $post_term->taxonomy == $taxonomy) {
                if (strcasecmp($post_term->name, $name) == 0) {
                    return $post_term;
                }
            }
        }
        return FALSE;
    }

    /**
     * Remove the term from the post
     * @param Wordpress_Term $term
     */
    public function delete(Wordpress_Term $term) {
        foreach ($this->_terms as $key => $post_term) {
            if ($post_term == $term) {
                unset($this->_terms[$key]);
                $this->_changed = TRUE;
                break;
            }
        }
    }

    public function serialize() {

        $terms = array();
        foreach ($this->_terms as $term) {
            if (!$term instanceof Wordpress_Term)
                throw new InvalidArgumentException('The post terms should be an array of Wordpress_Term object');

            //Create the object if is new
            if ($term->is_new()) {
                $search = $this->_post->site()->get_terms($term->taxonomy, 50, $offset, $term->name);
                foreach ($search as $related_term) {
                    if (strcasecmp($related_term->name, $term->name) == 0) {
                        $term = $related_term;
                        break;
                    }
                }

                if ($term->is_new())
                    $term->save();
            }

            if (!isset($terms[$term->taxonomy]))
                $terms[$term->taxonomy] = array();
            $terms[$term->taxonomy][] = $term->term_id;
        }
        return $terms;
    }

}