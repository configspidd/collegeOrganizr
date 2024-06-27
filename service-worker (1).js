const CACHE_NAME = 'college-organizer-cache-v1';
const urlsToCache = [
  '/',
  '/welcome.html',
  '/login.php',
  '/register.php',
  '/overview.php',
  '/courses.php',
  '/insidecourse.php',
  '/logout.php',
  '/session.php'
  // Add more pages and assets as needed
  '/styles/style.css', // Ensure this path is correct
  '/scripts/app.js' // Ensure this path is correct
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});
