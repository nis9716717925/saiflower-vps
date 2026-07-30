/**
 * Krish Florist - Theme Effects Engine
 * Handles particle animations based on global configuration.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Check if animation is enabled
    // This variable should be defined in the <head> by php
    if (typeof THEME_ANIMATION === 'undefined' || THEME_ANIMATION === 'none') return;

    createCanvasOverlay(THEME_ANIMATION);
});

function createCanvasOverlay(type) {
    const canvas = document.createElement('canvas');
    canvas.id = 'theme-effects-canvas';
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none'; // Click-through
    canvas.style.zIndex = '99999'; // On top of everything
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    let particles = [];
    let width = window.innerWidth;
    let height = window.innerHeight;

    function setCanvasSize() {
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = width;
        canvas.height = height;
    }
    setCanvasSize();

    // Handle Resize (debounced)
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            setCanvasSize();
            // Recalculate particle count proportional to viewport area (keeps perf predictable)
            initParticles();
        }, 120);
    });

    // Particle Config
    const config = {
        snow: { baseCount: 100, speed: 1.5, size: 3, color: 'rgba(255,255,255,0.8)' },
        rain: { baseCount: 300, speed: 15, size: 20, width: 1, color: 'rgba(174,194,224,0.6)' },
        petals: { baseCount: 30, speed: 2, size: 15, color: '#ffb7b2' }, // Pinkish
        sparkles: { baseCount: 50, speed: 0.5, size: 4, color: 'rgba(255,215,0,0.9)' }
    };

    const effect = config[type];
    if (!effect) return;

    // Compute particle count based on viewport area but cap it to reasonable limits
    function computeCount() {
        const area = Math.max(1024, width) * Math.max(768, height);
        const scale = Math.min(2, Math.max(0.5, area / (1365 * 768)));
        const count = Math.round(effect.baseCount * scale);
        return Math.min(800, Math.max(8, count));
    }

    function initParticles() {
        const count = computeCount();
        // Reuse array to avoid reallocations
        if (particles.length > count) particles.length = 0; // reset and recreate smaller set
        while (particles.length < count) {
            particles.push(new Particle(width, height, effect, type));
        }
    }

    initParticles();

    // Animation Loop (optimized: use indexed for-loop)
    function animate() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0, len = particles.length; i < len; i++) {
            const p = particles[i];
            p.update();
            p.draw(ctx);
        }
        requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
}

class Particle {
    constructor(w, h, config, type) {
        this.w = w;
        this.h = h;
        this.type = type;
        this.reset();
        // Scatter initial y so they don't all fall at once
        this.y = Math.random() * h;
    }

    reset() {
        this.x = Math.random() * this.w;
        this.y = -10;
        this.speed = (Math.random() * 1 + 0.5) * (this.type === 'rain' ? 10 : 1.5);
        this.size = Math.random() * 5 + 3;
        this.swing = Math.random() * 0.1 - 0.05;
        this.angle = Math.random() * Math.PI * 2;

        if (this.type === 'petals') {
            this.size = Math.random() * 10 + 10;
            this.color = ['#ffc3a0', '#ffafbd', '#e0c3fc', '#fff'][Math.floor(Math.random() * 4)];
        } else if (this.type === 'rain') {
            this.size = Math.random() * 10 + 10;
        } else if (this.type === 'sparkles') {
            this.color = `rgba(255, 215, 0, ${Math.random()})`;
        } else {
            this.color = 'rgba(255,255,255,0.8)';
        }
    }

    update() {
        this.y += this.speed;
        this.x += Math.sin(this.angle) * 0.5; // Slight sway
        this.angle += 0.02;

        if (this.type === 'petals') {
            this.x += Math.sin(this.angle) * 1.5;
            // Rotation
        }

        if (this.y > this.h) {
            this.reset();
        }
    }

    draw(ctx) {
        ctx.beginPath();
        if (this.type === 'rain') {
            ctx.strokeStyle = 'rgba(174,194,224,0.5)';
            ctx.lineWidth = 1;
            ctx.moveTo(this.x, this.y);
            ctx.lineTo(this.x, this.y + this.size);
            ctx.stroke();
        } else if (this.type === 'petals') {
            ctx.fillStyle = this.color;
            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(this.angle);
            ctx.ellipse(0, 0, this.size / 2, this.size / 4, 0, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        } else {
            // Snow / Sparkles (Circles)
            ctx.fillStyle = this.color;
            ctx.arc(this.x, this.y, this.type === 'sparkles' ? Math.random() * 3 : this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }
}
