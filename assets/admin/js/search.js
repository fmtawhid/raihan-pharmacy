var len = 0;
var clickLink = 0;
var search = null;
var process = false;

$('#searchInput').on('keydown', function (e) {
    var length = $('.search-list li').length;
    if (search != $(this).val() && process) {
        len = 0;
        clickLink = 0;
        $(`.search-list li:eq(${len}) a`).focus();
        $(`#searchInput`).focus();
    }
    //Down
    if (e.keyCode == 40 && length) {
        process = true;
        var contra = false;
        if (len < clickLink && clickLink < length) {
            len += 2;
        }
        $(`.search-list li[class="active"]`).removeClass('active');
        $(`.search-list li a[class="text-active"]`).removeClass('text-active');
        $(`.search-list li:eq(${len}) a`).focus().addClass('text-active');
        $(`.search-list li:eq(${len})`).addClass('active');
        $(`#searchInput`).focus();
        clickLink = len;
        if (!$(`.search-list li:eq(${clickLink}) a`).length) {
            $(`.search-list li:eq(${len})`).addClass('text-active');
        }
        len += 1;
        if (length == Math.abs(clickLink)) {
            len = 0;
        }
    }
    //Up
    else if (e.keyCode == 38 && length) {
        process = true;
        if (len > clickLink && len != 0) {
            len -= 2;
        }
        $(`.search-list li[class="active"]`).removeClass('active');
        $(`.search-list li a[class="text-active"]`).removeClass('text-active');
        $(`.search-list li:eq(${len}) a`).focus().addClass('text-active');
        $(`.search-list li:eq(${len})`).addClass('active');
        e.preventDefault();
        $(`#searchInput`).focus();
        clickLink = len;
        if (!$(`.search-list li:eq(${clickLink}) a`).length) {
            $(`.search-list li:eq(${len})`).addClass('text-active');
        }
        len -= 1;
        if (length == Math.abs(clickLink)) {
            len = 0;
        }
    }
    //Enter
    else if (e.keyCode == 13) {
        e.preventDefault();
        if ($(`.search-list li:eq(${clickLink}) a`).length && process) {
            $(`.search-list li:eq(${clickLink}) a`)[0].click();
        }
    }
    //Retry
    else if (e.keyCode == 8) {
        len = 0;
        clickLink = 0;
        $(`.search-list li:eq(${len}) a`).focus();
        $(`#searchInput`).focus();
    }
    search = $(this).val();
});

function filterSettings(query) {
    const normalizedQuery = String(query || '').toLowerCase();
    const filteredSettings = [];
    const matches = function (item) {
        if (!item || typeof item !== 'object') {
            return false;
        }

        const keywordMatch = (Array.isArray(item.keyword) ? item.keyword : [])
            .some(function (keyword) {
                return typeof keyword === 'string' && keyword.toLowerCase().includes(normalizedQuery);
            });
        const titleMatch = typeof item.title === 'string' && item.title.toLowerCase().includes(normalizedQuery);
        const subtitleMatch = typeof item.subtitle === 'string' && item.subtitle.toLowerCase().includes(normalizedQuery);

        return keywordMatch || titleMatch || subtitleMatch;
    };

    Object.keys(settingsData || {}).forEach(function (key) {
        const setting = settingsData[key];
        if (!setting || typeof setting !== 'object') {
            return;
        }

        if (Array.isArray(setting.submenu)) {
            setting.submenu.forEach(function (subItem) {
                if (matches(subItem)) {
                    filteredSettings.push(subItem);
                }
            });
        } else if (matches(setting)) {
            filteredSettings.push(setting);
        }
    });

    return filteredSettings;
}

function isEmpty(obj) {
    return Object.keys(obj).length === 0;
}

function renderSettings(filteredSettings, query) {
    var search_result_pane = $('.search-list');
    if (isEmpty(filteredSettings)) {
        $(search_result_pane).append(getEmptyMessage());
    } else {
        var index = 0;
        for (const key in filteredSettings) {
            if (Object.hasOwnProperty.call(filteredSettings, key)) {
                const element = filteredSettings[key];
                var setting = element;
                var url = null;
                var title = null;
                var tempRoute = null;
                var parent = null;

                tempRoute = setting.route_name;
                routeParams = null;
                if (setting.params) {
                    routeParams = setting.params;
                }
                title = setting.title;

                for (let route of routes) {
                    if (tempRoute in route) {
                        url = route[tempRoute];
                        if (routeParams) {
                            $.each(routeParams, function (paramKey, paramValue) {
                                url = url.replace(new RegExp(`{${paramKey}\\??}`, 'g'), paramValue);
                            });
                        }

                        url = url.replace(/\/+$/, '');
                        
                        var link = $(`.sidebar__menu a[href='${url}']`);
                        var text = $(link).parents('.sidebar-menu-item.sidebar-dropdown').find('.menu-title')
                            .first().text();

                        if (!text) {
                            text = 'Main Menu';
                            if (setting.subtitle) {
                                text = 'System Setting';
                            }
                        }
                        parent = text;
                    }
                }

                $(search_result_pane).append(`
                        <li>
                            <a class="search-list-link" href="${url}">
                                ${parent}
                                <p class="fw-bold text-color--3 d-block">${title}</p>
                            </a>
                        </li>
                    `);
            }
        }
    }
}

$('#searchInput').on('input', function () {
    $('.search-list').html('');

    var query = $(this).val().trim();
    if (!query) {
        return false;
    }

    var filteredData = filterSettings(query);
    renderSettings(filteredData, query);
});