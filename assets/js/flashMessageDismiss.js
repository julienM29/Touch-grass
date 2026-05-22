// Auto-dismiss des messages flash après 3 secondes
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';

            setTimeout(function() {
                message.remove();
            }, 500); // supprime après la transition
        }, 3000); // disparait après 3 secondes
    });
});
