/**
 * WordPress Header Image Support
 * 
 * Adds 'has-header-image' class to body when header image is present.
 * This works around PHP initialization timing issues where get_header_image()
 * returns empty during body_class() execution.
 */
(function () {
  'use strict';

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addHeaderClass);
  } else {
    addHeaderClass();
  }

  function addHeaderClass() {
    // Check if header image exists in the DOM
    var headerImage = document.querySelector('#wp-custom-header img, .custom-header-media img');

    if (headerImage && headerImage.src) {
      document.body.classList.add('has-header-image');
    }

    // Check for header video
    var headerVideo = document.querySelector('#wp-custom-header video, .custom-header-media video');

    if (headerVideo && headerVideo.src) {
      document.body.classList.add('has-header-video');
    }
    // Add theme-specific front-page class for Twenty Seventeen
    // This enables full-height header CSS rules
    // TODO: Make this dynamic when we have dynamic JS loading like CSS
    if (document.body.classList.contains('front') || document.body.classList.contains('home')) {
      document.body.classList.add('twentyseventeen-front-page');
    }
  }
})();
