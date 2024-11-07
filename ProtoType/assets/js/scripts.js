

// bsh thez l query taaml beha search request bl AJAX ml suggest w traja3ha in the form a table of content 
function fetchSuggestions(query) {
    const searchInput = document.querySelector('.search');
    const suggestionsList = document.getElementById('search-options');

    fetch(`/data/suggest?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            suggestionsList.innerHTML = '';
            if (data && data.length > 1 && searchInput.value.length > 1) {
                suggestionsList.style.display = 'block';
                searchInput.classList.add('no-bottom-border');
                data.forEach(item => createSuggestionItem(item, suggestionsList));
            } else {
                searchInput.classList.remove('no-bottom-border');
                suggestionsList.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
        });
}

function createSuggestionItem(item, suggestionsList) {
    const li = document.createElement('li');
    li.textContent = item.title;
    li.onclick = () => handleSuggestionClick(item, suggestionsList);
    suggestionsList.appendChild(li);
}

function handleSuggestionClick(item, suggestionsList) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/data/search';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'search_value';
    input.value = item.title;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    suggestionsList.style.display = 'none';
}
