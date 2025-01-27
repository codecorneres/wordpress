
if (window.location.search.includes('login=failed')) {
    const newUrl = window.location.origin + window.location.pathname;
    window.history.replaceState(null, '', newUrl);
}
if (window.location.search.includes('login=pending')) {
    const newUrl = window.location.origin + window.location.pathname;
    window.history.replaceState(null, '', newUrl);
}




