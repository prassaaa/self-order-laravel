import { ref, onUnmounted } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger)

// Animation presets for consistent timing and easing
export const animationPresets = {
  // Timing
  timing: {
    fast: 0.3,
    normal: 0.5,
    slow: 0.8,
    verySlow: 1.2,
  },

  // Easing curves
  easing: {
    smooth: 'power2.out',
    bounce: 'back.out(1.7)',
    elastic: 'elastic.out(1, 0.3)',
    sharp: 'power3.inOut',
  },

  // Common animations
  fadeIn: {
    from: { opacity: 0, y: 30 },
    to: { opacity: 1, y: 0 },
    duration: 0.6,
    ease: 'power2.out',
  },

  slideInLeft: {
    from: { opacity: 0, x: -50 },
    to: { opacity: 1, x: 0 },
    duration: 0.5,
    ease: 'power2.out',
  },

  slideInRight: {
    from: { opacity: 0, x: 50 },
    to: { opacity: 1, x: 0 },
    duration: 0.5,
    ease: 'power2.out',
  },

  scaleIn: {
    from: { opacity: 0, scale: 0.8 },
    to: { opacity: 1, scale: 1 },
    duration: 0.4,
    ease: 'back.out(1.7)',
  },

  staggerFadeIn: {
    from: { opacity: 0, y: 20 },
    to: { opacity: 1, y: 0 },
    duration: 0.5,
    ease: 'power2.out',
    stagger: 0.1,
  },
}

// Check for reduced motion preference
export const prefersReducedMotion = () => {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

// Main animation composable
export function useAnimations() {
  const timeline = ref<gsap.core.Timeline | null>(null)

  // Create a new timeline
  const createTimeline = (options?: gsap.TimelineVars) => {
    timeline.value = gsap.timeline(options)
    return timeline.value
  }

  // Animate element with fade in
  const fadeIn = (element: string | Element, options?: gsap.TweenVars): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(element, { opacity: 1 })
      return null
    }

    return gsap.fromTo(element,
      animationPresets.fadeIn.from,
      { ...animationPresets.fadeIn.to, ...options }
    )
  }

  // Animate element with slide in from left
  const slideInLeft = (element: string | Element, options?: gsap.TweenVars): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(element, { opacity: 1, x: 0 })
      return null
    }

    return gsap.fromTo(element,
      animationPresets.slideInLeft.from,
      { ...animationPresets.slideInLeft.to, ...options }
    )
  }

  // Animate element with slide in from right
  const slideInRight = (element: string | Element, options?: gsap.TweenVars): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(element, { opacity: 1, x: 0 })
      return null
    }

    return gsap.fromTo(element,
      animationPresets.slideInRight.from,
      { ...animationPresets.slideInRight.to, ...options }
    )
  }

  // Animate element with scale in
  const scaleIn = (element: string | Element, options?: gsap.TweenVars): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(element, { opacity: 1, scale: 1 })
      return null
    }

    return gsap.fromTo(element,
      animationPresets.scaleIn.from,
      { ...animationPresets.scaleIn.to, ...options }
    )
  }

  // Stagger animation for multiple elements
  const staggerFadeIn = (elements: string | Element[] | HTMLCollection, options?: gsap.TweenVars): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(elements, { opacity: 1, y: 0 })
      return null
    }

    // Convert HTMLCollection to Array if needed
    const elementsArray = elements instanceof HTMLCollection ? Array.from(elements) : elements

    return gsap.fromTo(elementsArray,
      animationPresets.staggerFadeIn.from,
      {
        ...animationPresets.staggerFadeIn.to,
        stagger: animationPresets.staggerFadeIn.stagger,
        ...options
      }
    )
  }

  // Scroll-triggered animation
  const animateOnScroll = (
    element: string | Element | Element[] | HTMLCollection,
    animation: gsap.TweenVars,
    trigger?: string | Element,
    options?: ScrollTrigger.Vars
  ): gsap.core.Tween | null => {
    if (prefersReducedMotion()) {
      gsap.set(element, animation)
      return null
    }

    // Convert HTMLCollection to Array if needed
    const elementsArray = element instanceof HTMLCollection ? Array.from(element) : element

    return gsap.fromTo(elementsArray,
      { opacity: 0, y: 50 },
      {
        ...animation,
        scrollTrigger: {
          trigger: trigger || (Array.isArray(elementsArray) ? elementsArray[0] : elementsArray),
          start: 'top 80%',
          end: 'bottom 20%',
          toggleActions: 'play none none reverse',
          ...options,
        }
      }
    )
  }

  // Hover animations
  const createHoverAnimation = (element: string | Element) => {
    if (prefersReducedMotion()) return

    const el = typeof element === 'string' ? document.querySelector(element) : element
    if (!el) return

    const handleMouseEnter = () => {
      gsap.to(el, {
        scale: 1.05,
        duration: animationPresets.timing.fast,
        ease: animationPresets.easing.smooth,
      })
    }

    const handleMouseLeave = () => {
      gsap.to(el, {
        scale: 1,
        duration: animationPresets.timing.fast,
        ease: animationPresets.easing.smooth,
      })
    }

    el.addEventListener('mouseenter', handleMouseEnter)
    el.addEventListener('mouseleave', handleMouseLeave)

    return () => {
      el.removeEventListener('mouseenter', handleMouseEnter)
      el.removeEventListener('mouseleave', handleMouseLeave)
    }
  }

  // Loading animation
  const createLoadingAnimation = (element: string | Element) => {
    if (prefersReducedMotion()) return

    return gsap.to(element, {
      rotation: 360,
      duration: 1,
      ease: 'none',
      repeat: -1,
    })
  }

  // Cleanup function
  const cleanup = () => {
    if (timeline.value) {
      timeline.value.kill()
    }
    ScrollTrigger.getAll().forEach(trigger => trigger.kill())
  }

  onUnmounted(() => {
    cleanup()
  })

  return {
    timeline,
    createTimeline,
    fadeIn,
    slideInLeft,
    slideInRight,
    scaleIn,
    staggerFadeIn,
    animateOnScroll,
    createHoverAnimation,
    createLoadingAnimation,
    cleanup,
    animationPresets,
    prefersReducedMotion,
  }
}

// Motion variants for @vueuse/motion
export const motionVariants = {
  initial: { opacity: 0, y: 50 },
  enter: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 600,
      ease: 'easeOut',
    }
  },
  leave: {
    opacity: 0,
    y: -50,
    transition: {
      duration: 400,
      ease: 'easeIn',
    }
  },
}

// Stagger variants
export const staggerVariants = {
  initial: { opacity: 0, y: 20 },
  enter: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 100,
      duration: 500,
      ease: 'easeOut',
    }
  }),
}
