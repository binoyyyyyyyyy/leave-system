/**
 * Depends on api_client.js. Polls JSON GET endpoints while the tab is visible
 * so open pages pick up changes from other users/tabs without manual refresh.
 */
(function () {
    if (typeof window.LSApi === 'undefined') {
        return;
    }

    window.LSLive = {
        /**
         * @param {string} path API path relative to data-api-base (e.g. "admin/leave_requests.php")
         * @param {number} intervalMs
         * @param {function(Object): void} onSuccess receives full parsed JSON body when success is true
         * @returns {function(): void} stop
         */
        pollGet: function (path, intervalMs, onSuccess) {
            var timer = null;
            var stopped = false;

            function tick() {
                if (stopped || document.visibilityState !== 'visible') {
                    return;
                }
                LSApi.get(path).then(function (res) {
                    if (stopped) {
                        return;
                    }
                    if (res.ok && res.data && res.data.success) {
                        onSuccess(res.data);
                    }
                });
            }

            timer = setInterval(tick, intervalMs);
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    tick();
                }
            });

            return function stop() {
                stopped = true;
                if (timer !== null) {
                    clearInterval(timer);
                }
            };
        }
    };
})();
