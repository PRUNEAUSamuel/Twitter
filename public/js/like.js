document.addEventListener("DOMContentLoaded", function () {
    const likeButtons = document.querySelectorAll('[id^="like-button-"]');

    likeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const tweetId = button.getAttribute('data-tweet-id');
            const likesCountElement = document.getElementById('likes-count-' + tweetId);
            const buttonContent = document.querySelector('.like-div-' + tweetId);
            const image = document.querySelectorAll('.like-img-' + tweetId);

            // Déterminer si l'utilisateur a déjà liké ou non
            const liked = button.getAttribute('data-liked') === 'true';

            // Envoyer la requête AJAX pour ajouter/retirer le like
            fetch('/tweets/like/' + tweetId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest', // Indiquer qu'il s'agit d'une requête AJAX
                    'Accept': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Mettre à jour le nombre de likes
                    likesCountElement.textContent = data.likesCount;

                    // Mettre à jour le texte du bouton (Like ou Dislike)

                    
                    if (data.liked) {
                        buttonContent.setAttribute('data-liked', 'true');
                        image.forEach(image => {
                            image.classList.remove('far');
                            image.classList.add('fas');
                        })
                    } else {
                        buttonContent.setAttribute('data-liked', 'false');
                        image.forEach(image => {
                            image.classList.remove('fas');
                            image.classList.add('far');
                        })
                    }
                } else {
                    alert('Une erreur est survenue.');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
            });
        });
    });
});