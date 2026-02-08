document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('form[data-delete-file]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const fileName = form.querySelector('input[name="delete_file"]').value;
            const deleteButton = form.querySelector('button[type="submit"]');
            const row = form.closest('tr');

            deleteButton.disabled = true;
            deleteButton.textContent = 'Deleting...';
            deleteButton.classList.remove('bg-amber-800', 'hover:bg-red-600');
            deleteButton.classList.add('bg-red-500');

            const startTime = Date.now();

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            }).then(response => {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = Math.max(0, 1400 - elapsedTime);

                setTimeout(() => {
                    if (response.ok) {
                        deleteButton.textContent = 'Deleted';
                        deleteButton.classList.remove('bg-red-500');
                        deleteButton.classList.add('bg-gray-500');
                        setTimeout(() => {
                            row.remove();
                        }, 800);
                    } else {
                        deleteButton.textContent = 'Delete';
                        deleteButton.disabled = false;
                        deleteButton.classList.remove('bg-red-500');
                        deleteButton.classList.add('bg-amber-800', 'hover:bg-red-600');
                    }
                }, remainingTime);
            }).catch(error => {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = Math.max(0, 1400 - elapsedTime);

                setTimeout(() => {
                    deleteButton.textContent = 'Delete';
                    deleteButton.disabled = false;
                    deleteButton.classList.remove('bg-red-500');
                    deleteButton.classList.add('bg-amber-800', 'hover:bg-red-600');
                }, remainingTime);
            });
        });
    });
});

function copyLink() {
    const link = document.getElementById('generatedLink');
    link.select();
    document.execCommand('copy');
    const copyButton = document.querySelector('.btn-primary');
    copyButton.innerText = 'Copied!';
    copyButton.classList.remove('bg-amber-500', 'hover:bg-amber-700');
    copyButton.classList.add('bg-green-500');
    setTimeout(() => {
        copyButton.innerText = 'Copy';
        copyButton.classList.remove('bg-green-500');
        copyButton.classList.add('bg-amber-500', 'hover:bg-amber-700');
    }, 4000);
}

function copyCode() {
    const codeContent = document.getElementById('codeContent').innerText;
    const tempTextArea = document.createElement('textarea');
    tempTextArea.value = codeContent;
    document.body.appendChild(tempTextArea);
    tempTextArea.select();
    document.execCommand('copy');
    document.body.removeChild(tempTextArea);
    const copyButton = document.querySelector('.btn-primary');
    copyButton.innerText = 'Copied!';
    copyButton.classList.remove('bg-amber-500', 'hover:bg-amber-700');
    copyButton.classList.add('bg-green-500');
    setTimeout(() => {
        copyButton.innerText = 'Copy';
        copyButton.classList.remove('bg-green-500');
        copyButton.classList.add('bg-amber-500', 'hover:bg-amber-700');
    }, 4000);
}

document.addEventListener("DOMContentLoaded", () => {
    const scrollTopButton = document.getElementById("scrollTopButton");

    // Check if the button exists
    if (!scrollTopButton) {
        console.error("scrollTopButton not found!");
        return;
    }

    // Listen for the scroll event
    window.addEventListener("scroll", () => {
        if (window.scrollY > 200) {
            scrollTopButton.classList.remove("hidden");
        } else {
            scrollTopButton.classList.add("hidden");
        }
    });

    // Button click event
    scrollTopButton.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    });
});