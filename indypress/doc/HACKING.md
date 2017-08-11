# Code Overview #

## Publication ##

Publication is handled by `classes/publication.*` .

### TinyMCE ###

Lot of code is to setup tinyMCE properly; when adding buttons (for video
upload, embed, etc.) a trick is used: as tinymce wipes [shorttags], we add
things like `<a href="our/resource"><img src="a_file_related_to_our_shortcode"
/></a>` to editor; when receiving it as `$_POST`, we'll replace it with our
shortcode.

### Preview ###

Post preview is done in the simpler way: just publish it, but set
`post_status=draft`. Then link it.
This raises an issue: we shall delete those drafts after some time. So, every
time a draft is created, its removal is scheduled (an hour later).

It should work fine this way, but we need, just in case, a method of "remove
all drafts that, for some reason, we don't yet". It's simple to do this for the
publication author. But doing this for every author will delete admin's drafts,
too. There's no solution for this, still.


### Embeds ###

Embeds are handled using wordpress embeds: <http://codex.wordpress.org/Embeds>.
Just put a url and wordpress will do the rest. We check if that code is a valid
embed both client-side (with JS) and server-side (checking if we have a
provider/handler for that).
When a file contains an embed, a post meta is added: `indypress_embed=1`.
This is used to add class `media_embed` to posts' div.

### Events ###

Events have a different post type, `indypress_event` and two meta fields (start
and end timestamps).
This allows for cleanness and, with WP >= 3.1, both
`single-indypress_event.php` and `archive-indypress_event.php` templates.
To handle this post type there are some functions like
`the_event_information()` and the filter `the_event_information`. Read more in
Styling section.

#### Permalink ####

Events permalink can be customized in Visualization page, Event visualization
section. Note that not every element of the standard wordpress permalink
structure (for example `%year%`) is supported. At the moment, only `%post_id%`
is supported. It is however trivial to add new tags if needed.  Look at
`event_link` function in `common.class.php`

#### Admin panel ####

Adding events from the admin panel can be tricky, because the admin interface
doesn't do proper sanity checks (duration, end after start, categories).
That's the reason why there is an option to "disable" it.
Keep in mind that that option does not actually prevent a smart user (which has
publishing capabilities) from entering the link and adding a new page. If you
want to deny it, you have to use something else.

## Comments ##

Comments can be hidden or promoted.
This is done using `comment_type`.

### promoted comments ###

They are meant to be "integrated" in the post, as they make it more complete.
By default, IndyPress will indeed show them at the bottom of the post content.
Since its style is poor, you can disable indypress from doing so, and do it
yourself in functions.php.

# Styling (theme designers read here) #

There are several things you can/should theme when adapting a theme to
IndyPress:

* you can use `media_embed` class in posts that contain an embed
* you should theme tooltips in publication page (if JS is enabled)
* you can use `the_event_information()` to show event information wherever you
  want. Checkout the Visualization => EventInformation option
* you can get a link that (if the user has appropriate permission) will change
  post status with `indypress_hide_post_href( $pid, $status )`
* you can customize event information through the filter
  `the_event_information`
* you can customize promoted comments display through the standard
  `the_content` filter
* you can use these **Template tags**:
    - you can use `is_hidden_post()` as a Template Tag
    - you can use `is_indypress_event()` as a Template Tag

# DB #

If you have to work with the DB, know that:

* In `_posts table`:
  - `post_status` can be `hide` or `premoderate`, too. Hide is for 'hidden post',
     and premoderate is for premoderated posts!
  -  `post_type` can be `indypress_event`, for events. See postmeta below
* In `_postmeta` table, events have 2 meta fields, `event_start` and `event_end`;
  their value is a UNIX timestamp of the event start and end
* In `_comments` table, `comment_type` can be `hidden`, if the comment is hidden.
  It is empty otherwise.
