{{-- Three.js Optimizations - Include this before your Three.js scripts --}}
<script>
// Three.js Performance Optimizer
window.ThreeOptimizer = {
    // Reduce geometry complexity
    optimizeGeometry: function(geometry, maxVertices = 1000) {
        if (geometry.attributes.position && geometry.attributes.position.count > maxVertices) {
            // Simplify by using BufferGeometryUtils or reducing segments
            return geometry;
        }
        return geometry;
    },
    
    // Reduce particle count based on device performance
    getOptimalParticleCount: function(preferredCount) {
        const isMobile = window.matchMedia('(pointer: coarse)').matches;
        const isLowPower = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4;
        
        if (isMobile) return Math.min(preferredCount, 10);
        if (isLowPower) return Math.min(preferredCount, 15);
        return preferredCount;
    },
    
    // Get optimal geometry segments
    getOptimalSegments: function(preferred) {
        const isMobile = window.matchMedia('(pointer: coarse)').matches;
        if (isMobile) return Math.max(4, Math.floor(preferred / 2));
        return preferred;
    },
    
    // Visibility-based animation controller
    createVisibilityController: function(renderer, scene, camera) {
        let isActive = true;
        let animationId = null;
        
        // Intersection Observer for lazy rendering
        const canvas = renderer.domElement;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                isActive = entry.isIntersecting;
                if (!isActive && animationId) {
                    cancelAnimationFrame(animationId);
                    animationId = null;
                } else if (isActive && !animationId) {
                    animate();
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(canvas.parentElement || canvas);
        
        // Visibility API
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                isActive = false;
                if (animationId) {
                    cancelAnimationFrame(animationId);
                    animationId = null;
                }
            } else {
                isActive = true;
                if (!animationId) animate();
            }
        });
        
        // Frame skip for performance
        let frameCount = 0;
        const frameSkip = window.matchMedia('(pointer: coarse)').matches ? 2 : 1;
        
        function animate() {
            if (!isActive) return;
            animationId = requestAnimationFrame(animate);
            
            frameCount++;
            if (frameCount % frameSkip !== 0) return;
            
            renderer.render(scene, camera);
        }
        
        return { animate, observer };
    }
};

// Chart.js Performance Optimizer
window.ChartOptimizer = {
    // Default optimized options
    getOptimizedOptions: function(customOptions = {}) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            transitions: { active: { animation: { duration: 0 } } },
            plugins: {
                legend: { display: false }
            },
            ...customOptions
        };
    },
    
    // Optimize data points
    optimizeDataPoints: function(data, maxPoints = 30) {
        if (data.length <= maxPoints) return data;
        
        // Sample data evenly
        const step = Math.ceil(data.length / maxPoints);
        return data.filter((_, i) => i % step === 0);
    }
};
</script>
