# Readme #

## Feature list ##

* Open publishing
* Different post types: ordinary post, events, liveblog entries (liveblogging
  plugin required)
* Rich interface for open publishing (see below)
* Posts and comments can be hidden (instead of deleted)
* Optional premoderation: see its own section
* Widget for displaying next events

### Open Publish form features ###

* TinyMCE interface for rich text editing, upload and media embed
* Different "kinds" of categorization: by topic and by localization
* Form validation: if the user is missing some fields, JS warns it immediately
  without making it wait an entire POST
* HTML5 form attributes (newest browser will autovalidate some fields)

## What is premoderation? ##

The simplest workflow is 1. publish; the post is shown on the home page 2. hide it if it's not ok for the policy
With a premoderation workflow you can make a different workflow: 1. publish;
the post is shown, but it's not on the top 2.  if it is good, approve, so it
will get to a better position. If it is not good, hide.
So you can keep open publishing, presenting the user a "clean" newswire,
without denying him to see *any* article that get posted

## Non-features ##

Indypress does NOT have these features:
* UI Internationalization: it is a TODO
* Custom taxonomy/categorization systems behind the topic/localization one
* Multi language articles: this is not even in our wishlist.
