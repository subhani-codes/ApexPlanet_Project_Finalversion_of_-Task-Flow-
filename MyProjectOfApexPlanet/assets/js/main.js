// Dark mode toggle
function toggleDarkMode() {
    var isDark = document.body.classList.toggle('dark');
    localStorage.setItem('taskflow_dark', isDark);
    updateDarkBtn(isDark);
}

function updateDarkBtn(isDark) {
    var btn = document.getElementById('darkToggleBtn');
    if(btn) {
        btn.textContent = isDark ? '☀️ Light' : '🌙 Dark';
    }
}

// Load dark mode preference on page load
document.addEventListener('DOMContentLoaded', function() {
    var isDark = localStorage.getItem('taskflow_dark') === 'true';
    if(isDark) {
        document.body.classList.add('dark');
    }
    updateDarkBtn(isDark);

    // Auto hide alerts after 3 seconds
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() { 
                alert.remove(); 
            }, 500);
        }, 3000);
    });
});

// Confirm delete
function confirmDelete() {
    return confirm('Are you sure you want to delete this task?');
}
// ==========================================================================
// DYNAMIC UI COMPONENT MANIPULATION: SCHEDULER MANTRAS
// ==========================================================================
document.addEventListener('DOMContentLoaded', function() {
    const quotesCollection = [
        "💡 Consistency outperforms raw talent. Keep compiling clean routines!",
        "🚀 Make each code refactoring run better than your previous repository push.",
        "⚡ Great user interfaces are built block-by-block with intentional focus.",
        "🎯 Break down monumental application pipelines into small, logical features.",
        "🛡️ Secure your data boundaries first. Performance follows defensive code formatting."
    ];

    const quoteElementTarget = document.getElementById('motivationalQuote');
    
    if(quoteElementTarget) {
        let executionIndex = 0;

        function cycleMotivationalQuoteString() {
            // Apply simple look animation behavior shifts natively using layer changes
            quoteElementTarget.style.opacity = '0';
            
            setTimeout(function() {
                quoteElementTarget.textContent = quotesCollection[executionIndex];
                quoteElementTarget.style.opacity = '1';
                
                // Advance or loop array counters safely
                executionIndex = (executionIndex + 1) % quotesCollection.length;
            }, 400); // Wait for the transition fade duration timing interval
        }

        // Deploy initial runtime function stack loop immediately
        cycleMotivationalQuoteString();
        
        // Execute background asynchronous loop cycles every 5000 milliseconds
        setInterval(cycleMotivationalQuoteString, 5000);
    }
});



// ===============================
// Professional Button Loading
// ===============================

document.addEventListener("DOMContentLoaded", function () {

    const forms = document.querySelectorAll("form");

    forms.forEach(form => {

        form.addEventListener("submit", function () {

            const btn = form.querySelector("button[type='submit'], input[type='submit']");

            if (btn) {

                btn.dataset.originalText = btn.innerHTML;

                btn.innerHTML = "⏳ Please Wait...";

                btn.disabled = true;

                btn.style.opacity = "0.7";

                btn.style.cursor = "not-allowed";

            }

        });

    });

});