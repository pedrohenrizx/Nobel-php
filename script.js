document.addEventListener('DOMContentLoaded', () => {
    const candidatesGrid = document.getElementById('candidates-grid');
    const loadingDiv = document.getElementById('loading');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    const successMessage = document.getElementById('success-message');

    // Fetch candidates from API
    const fetchCandidates = async () => {
        try {
            const response = await fetch('/api/candidates');
            if (!response.ok) {
                throw new Error('Failed to fetch candidates');
            }
            const data = await response.json();
            renderCandidates(data);
        } catch (error) {
            showError('Could not load candidates. Please try again later.');
            console.error('Error fetching candidates:', error);
        } finally {
            loadingDiv.classList.add('hidden');
        }
    };

    // Render candidates to the DOM
    const renderCandidates = (candidates) => {
        candidatesGrid.innerHTML = ''; // Clear existing

        if (candidates.length === 0) {
            candidatesGrid.innerHTML = '<p class="col-span-full text-center text-gray-500">No candidates found.</p>';
        } else {
            candidates.forEach(candidate => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-lg shadow-md overflow-hidden flex flex-col transition-transform transform hover:scale-105';

                card.innerHTML = `
                    <div class="p-6 flex-grow flex flex-col">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">${escapeHTML(candidate.name)}</h2>
                        <p class="text-gray-600 mb-4 flex-grow">${escapeHTML(candidate.description)}</p>
                        <div class="flex justify-between items-center mt-auto">
                            <span class="text-lg font-semibold text-blue-600">Votes: <span id="votes-${candidate.id}">${candidate.votes}</span></span>
                            <button onclick="vote(${candidate.id})" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                                Vote
                            </button>
                        </div>
                    </div>
                `;
                candidatesGrid.appendChild(card);
            });
        }

        candidatesGrid.classList.remove('hidden');
    };

    // Vote for a candidate
    window.vote = async (id) => {
        try {
            hideMessages();
            const response = await fetch('/api/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to submit vote');
            }

            const result = await response.json();
            if (result.success) {
                showSuccess();
                // Refresh candidates to get updated vote counts
                fetchCandidates();
            }
        } catch (error) {
            showError(error.message);
            console.error('Error submitting vote:', error);
        }
    };

    // Helper functions
    const escapeHTML = (str) => {
        return str.replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    };

    const showError = (msg) => {
        errorText.textContent = msg;
        errorMessage.classList.remove('hidden');
    };

    const showSuccess = () => {
        successMessage.classList.remove('hidden');
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 3000);
    };

    const hideMessages = () => {
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
    };

    // Initial fetch
    fetchCandidates();
});
