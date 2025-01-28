// Client-side Form Validation
document.getElementById('donationForm').addEventListener('submit', function(e){
  let valid = true;

  // Clear previous error messages
  document.getElementById('nameError').innerText = '';
  document.getElementById('emailError').innerText = '';
  document.getElementById('amountError').innerText = '';

  // Validate Name
  const name = document.getElementById('name').value.trim();
  if(name === ''){
      document.getElementById('nameError').innerText = 'Name is required.';
      valid = false;
  } else if(name.length < 2){
      document.getElementById('nameError').innerText = 'Name must be at least 2 characters.';
      valid = false;
  }

  // Validate Email
  const email = document.getElementById('email').value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if(email === ''){
      document.getElementById('emailError').innerText = 'Email is required.';
      valid = false;
  } else if(!emailRegex.test(email)){
      document.getElementById('emailError').innerText = 'Invalid email format.';
      valid = false;
  }

  // Validate Amount
  const amount = document.getElementById('amount').value.trim();
  if(amount === ''){
      document.getElementById('amountError').innerText = 'Donation amount is required.';
      valid = false;
  } else if(isNaN(amount) || Number(amount) < 100){
      document.getElementById('amountError').innerText = 'Minimum donation is NGN 100.';
      valid = false;
  }

  if(!valid){
      e.preventDefault();
  }
});

// Confetti Animation
window.onload = function(){
  <?php if(isset($_SESSION['success'])): ?>
      launchConfetti();
  <?php endif; ?>
};

function launchConfetti() {
  const canvas = document.getElementById('confettiCanvas');
  const ctx = canvas.getContext('2d');
  const confetti = [];
  const colors = ['#f39c12', '#e74c3c', '#8e44ad', '#2ecc71', '#3498db'];

  // Set canvas size
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  // Confetti Particle Class
  class ConfettiParticle {
      constructor(){
          this.x = Math.random() * canvas.width;
          this.y = Math.random() * canvas.height - canvas.height;
          this.size = Math.random() * 5 + 5;
          this.weight = Math.random() * 1 + 1;
          this.direction = Math.random() * 360;
          this.color = colors[Math.floor(Math.random() * colors.length)];
          this.opacity = Math.random() * 0.5 + 0.5;
      }

      update(){
          this.y += this.weight;
          this.x += Math.sin(this.direction) * 0.5;
          if(this.y > canvas.height){
              this.y = -this.size;
              this.x = Math.random() * canvas.width;
          }
      }

      draw(){
          ctx.fillStyle = `rgba(${hexToRgb(this.color)}, ${this.opacity})`;
          ctx.beginPath();
          ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
          ctx.fill();
      }
  }

  // Convert Hex to RGB
  function hexToRgb(hex) {
      const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
      hex = hex.replace(shorthandRegex, function(m, r, g, b) {
          return r + r + g + g + b + b;
      });

      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? 
        `${parseInt(result[1], 16)},${parseInt(result[2], 16)},${parseInt(result[3], 16)}` 
        : null;
  }

  // Create Confetti Particles
  function initConfetti(){
      for(let i=0; i < 150; i++){
          confetti.push(new ConfettiParticle());
      }
  }

  // Animate Confetti
  function animateConfetti(){
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      confetti.forEach(particle => {
          particle.update();
          particle.draw();
      });
      requestAnimationFrame(animateConfetti);
  }

  initConfetti();
  animateConfetti();
}
