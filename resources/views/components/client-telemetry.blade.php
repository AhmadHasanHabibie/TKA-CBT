<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var trackUrl = '{{ route("telemetry.track") }}';

    var state = {
        log_id: {{ Js::from(session('visitor_log_id')) }},
        visited_url: window.location.href,
        client_ip: '',
        client_location: '',
        client_district: '',
        client_city: '',
        client_region: '',
        client_country: '',
        client_isp: '',
        client_org: '',
        client_latitude: '',
        client_longitude: '',
        client_timezone: '',
        device_fingerprint: '',
        device_model: '',
        platform_version: '',
        architecture: '',
        browser_full_version: '',
        gpu_renderer: '',
        cpu_cores: '',
        ram_gb: '',
        screen_resolution: '',
        battery_level: '',
        battery_charging: '',
        network_type: '',
        network_downlink: '',
        network_rtt: ''
    };

    window.__tkaTelemetry = state;

    function hashString(str) {
        var h1 = 0xdeadbeef ^ str.length;
        var h2 = 0x41c6ce57 ^ str.length;
        for (var i = 0, ch; i < str.length; i++) {
            ch = str.charCodeAt(i);
            h1 = Math.imul(h1 ^ ch, 2654435761);
            h2 = Math.imul(h2 ^ ch, 1597334677);
        }
        h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507) ^ Math.imul(h2 ^ (h2 >>> 13), 3266489909);
        h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507) ^ Math.imul(h1 ^ (h1 >>> 13), 3266489909);
        var hex1 = (h1 >>> 0).toString(16).padStart(8, '0');
        var hex2 = (h2 >>> 0).toString(16).padStart(8, '0');
        return (hex1 + hex2).toLowerCase();
    }

    function getCanvasSignature() {
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 240;
            canvas.height = 60;
            var ctx = canvas.getContext('2d');
            if (!ctx) return 'no-canvas';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = '#f60';
            ctx.fillRect(100, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.font = '14px Arial, sans-serif';
            ctx.fillText('TKA-Device-ID-98765', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.font = '16px Georgia, serif';
            ctx.fillText('TKA-Device-ID-98765', 4, 42);
            ctx.beginPath();
            ctx.arc(50, 30, 18, 0, Math.PI * 2, true);
            ctx.closePath();
            ctx.fill();
            return canvas.toDataURL();
        } catch (e) {
            return 'canvas-err';
        }
    }

    function getWebglInfo() {
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return { renderer: '', vendor: '' };
            var dbg = gl.getExtension('WEBGL_debug_renderer_info');
            var renderer = dbg ? gl.getParameter(dbg.UNMASKED_RENDERER_WEBGL) : gl.getParameter(gl.RENDERER);
            var vendor = dbg ? gl.getParameter(dbg.UNMASKED_VENDOR_WEBGL) : gl.getParameter(gl.VENDOR);
            return {
                renderer: renderer ? String(renderer) : '',
                vendor: vendor ? String(vendor) : ''
            };
        } catch (e) {
            return { renderer: '', vendor: '' };
        }
    }

    function collectSyncDiagnostics() {
        try {
            state.client_timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {}

        state.cpu_cores = navigator.hardwareConcurrency || '';
        state.ram_gb = navigator.deviceMemory || '';

        if (window.screen) {
            state.screen_resolution = window.screen.width + 'x' + window.screen.height;
        }

        var webgl = getWebglInfo();
        state.gpu_renderer = webgl.renderer || '';

        var canvasSig = getCanvasSignature();
        var rawFp = [
            canvasSig,
            webgl.renderer,
            webgl.vendor,
            state.screen_resolution,
            window.screen ? (window.screen.colorDepth || 24) : 24,
            window.devicePixelRatio || 1,
            state.cpu_cores,
            state.ram_gb,
            navigator.platform || ''
        ].join('||');

        state.device_fingerprint = hashString(rawFp);

        var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn) {
            state.network_type = conn.effectiveType || conn.type || '';
            if (typeof conn.downlink === 'number') {
                state.network_downlink = conn.downlink;
            }
            if (typeof conn.rtt === 'number') {
                state.network_rtt = conn.rtt;
            }
        }
    }

    function syncHiddenInputs() {
        var forms = document.querySelectorAll('form');
        var keys = [
            'client_ip',
            'client_location',
            'client_district',
            'client_city',
            'client_region',
            'client_country',
            'client_isp',
            'client_org',
            'client_latitude',
            'client_longitude',
            'client_timezone',
            'device_fingerprint',
            'device_model',
            'platform_version',
            'architecture',
            'browser_full_version',
            'gpu_renderer',
            'cpu_cores',
            'ram_gb',
            'screen_resolution',
            'battery_level',
            'battery_charging',
            'network_type',
            'network_downlink',
            'network_rtt'
        ];

        keys.forEach(function (k) {
            var byId = document.getElementById(k);
            if (byId && state[k] !== '' && state[k] !== null && state[k] !== undefined) {
                byId.value = state[k];
            }
        });

        forms.forEach(function (form) {
            if ((form.method || '').toUpperCase() !== 'POST') return;
            keys.forEach(function (k) {
                if (state[k] === '' || state[k] === null || state[k] === undefined) return;
                var input = form.querySelector('input[name="' + k + '"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = k;
                    form.appendChild(input);
                }
                input.value = state[k];
            });
        });
    }

    function sendTelemetry() {
        syncHiddenInputs();
        fetch(trackUrl, {
            method: 'POST',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(state)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.log_id) {
                state.log_id = res.log_id;
            }
        })
        .catch(function () {});
    }

    function initTelemetry() {
        collectSyncDiagnostics();
        syncHiddenInputs();

        var promises = [];

        if (navigator.userAgentData && typeof navigator.userAgentData.getHighEntropyValues === 'function') {
            var uaPromise = navigator.userAgentData.getHighEntropyValues([
                'model',
                'platform',
                'platformVersion',
                'architecture',
                'fullVersionList'
            ]).then(function (ua) {
                if (ua.model) {
                    state.device_model = ua.model;
                }
                if (ua.platform) {
                    state.platform_version = ua.platform + (ua.platformVersion ? ' ' + ua.platformVersion : '');
                }
                if (ua.architecture) {
                    state.architecture = ua.architecture;
                }
                if (Array.isArray(ua.fullVersionList)) {
                    var realBrand = ua.fullVersionList.find(function (b) {
                        return b.brand && !/Not.*Brand/i.test(b.brand) && !/Chromium/i.test(b.brand);
                    }) || ua.fullVersionList.find(function (b) {
                        return b.brand && !/Not.*Brand/i.test(b.brand);
                    });
                    if (realBrand) {
                        state.browser_full_version = realBrand.brand + ' ' + realBrand.version;
                    }
                }
            }).catch(function () {});
            promises.push(uaPromise);
        }

        if (navigator.getBattery && typeof navigator.getBattery === 'function') {
            var batPromise = navigator.getBattery().then(function (bat) {
                if (bat) {
                    if (typeof bat.level === 'number') {
                        state.battery_level = Math.round(bat.level * 100);
                    }
                    if (typeof bat.charging === 'boolean') {
                        state.battery_charging = bat.charging ? 1 : 0;
                    }
                }
            }).catch(function () {});
            promises.push(batPromise);
        }

        Promise.allSettled(promises).then(function () {
            sendTelemetry();
        });

        fetch('https://ipwho.is/')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    state.client_ip = data.ip || '';
                    var parts = [data.city, data.region, data.country].filter(Boolean);
                    state.client_location = parts.join(', ');
                    state.client_city = data.city || '';
                    state.client_region = data.region || '';
                    state.client_country = data.country || '';
                    if (data.connection) {
                        state.client_isp = data.connection.isp || '';
                        state.client_org = data.connection.org || (data.connection.asn ? 'AS' + data.connection.asn : '');
                    }
                    sendTelemetry();
                }
            })
            .catch(function () {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTelemetry);
    } else {
        initTelemetry();
    }
})();
</script>
