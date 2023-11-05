(function (window, document) {

    var layout   = document.getElementById('layout'),
        menu     = document.getElementById('menu'),
        logsToggleButton= document.getElementById('logs-toggle-button'),
        logsContainer= document.getElementById('logs-container'),
        menuLink = document.getElementById('menuLink');

    function toggleClass(element, className) {
        var classes = element.className.split(/\s+/),
            length = classes.length,
            i = 0;

        for(; i < length; i++) {
          if (classes[i] === className) {
            classes.splice(i, 1);
            break;
          }
        }
        // The className is not found
        if (length === classes.length) {
            classes.push(className);
        }

        element.className = classes.join(' ');
    }

    menuLink.onclick = function (e) {
        var active = 'active';

        e.preventDefault();
        toggleClass(layout, active);
        toggleClass(menu, active);
        toggleClass(menuLink, active);
    };

    logsToggleButton.onclick = function (e) {
        const $container = $(logsContainer);
        $container.toggle()

        if ($container.css('display') !== 'none') {
            this.innerHTML = 'Hide Debug Logs';
        } else {
            this.innerHTML = 'Show Debug Logs'
        }
    };

    window.spinnerOn = function(btn) {
        $('#mainTitle').addClass('blink_me');
        $('#menuLink').addClass('blink_me');
        $('#loader').show();
        if (btn) {
            $(btn).addClass('btn-in-action')
        }
    };

    window.spinnerOff = function(btn) {
        $('#mainTitle').removeClass('blink_me');
        $('#menuLink').removeClass('blink_me');
        $('#loader').hide();
        if (btn) {
            $(btn).removeClass('btn-in-action')
        }
    };

    window.$('.btn-actionable').on('click', function(el) {
        $(el.target).addClass('btn-in-action');
    });

}(this, this.document));
