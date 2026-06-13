/**
 * Leave-system JSON API helper. Set on <body data-api-base="../api/"> (path to api/ folder).
 */
(function () {
    function getBase() {
        var b = document.body && document.body.getAttribute('data-api-base');
        return b || 'api/';
    }

    function join(base, path) {
        if (base.slice(-1) !== '/') base += '/';
        if (path.indexOf('/') === 0) path = path.slice(1);
        return base + path;
    }

    window.LSApi = {
        base: function () {
            return getBase();
        },
        url: function (path) {
            return join(getBase(), path);
        },
        /**
         * @param {string} path relative to api/, e.g. "teacher/dashboard.php"
         * @param {RequestInit} [options]
         */
        request: async function (path, options) {
            options = options || {};
            var o = Object.assign({ credentials: 'same-origin' }, options);
            if (!o.headers) o.headers = {};
            if (o.body != null && typeof o.body === 'object' && !(o.body instanceof FormData) && typeof o.body !== 'string') {
                o.headers['Content-Type'] = 'application/json';
                o.body = JSON.stringify(o.body);
            }
            var res = await fetch(this.url(path), o);
            var data = {};
            try {
                data = await res.json();
            } catch (e) {
                data = {};
            }
            return { ok: res.ok, status: res.status, data: data };
        },
        get: function (path) {
            return this.request(path, { method: 'GET' });
        },
        post: function (path, body) {
            return this.request(path, { method: 'POST', body: body });
        }
    };
})();
