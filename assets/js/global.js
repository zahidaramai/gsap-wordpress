/**
 * GSAP Global Animations
 *
 * This file contains your custom GSAP animations that will load globally on your WordPress site.
 * Edit this file through the GSAP > Customize tab in your WordPress admin.
 *
 * @package GSAP_For_WordPress
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {

    /**
     * Example: Basic Fade-in Animation
     * Uncomment the code below to see it in action
     */
    /*
    gsap.from('.fade-in', {
        duration: 1,
        opacity: 0,
        y: 30,
        stagger: 0.2,
        ease: 'power2.out'
    });
    */

    /**
     * Example: ScrollTrigger Animation
     * Make sure ScrollTrigger is enabled in GSAP settings
     */
    /*
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        gsap.from('.scroll-animate', {
            scrollTrigger: {
                trigger: '.scroll-animate',
                start: 'top 80%',
                end: 'bottom 20%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            opacity: 0,
            y: 50,
            stagger: 0.2
        });
    }
    */

    /**
     * Add Your Custom Animations Below
     */

    // Your code here...

});
