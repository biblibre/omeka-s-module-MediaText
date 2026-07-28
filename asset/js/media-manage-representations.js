$(document).ready(function() {
    'use strict';

    const representationList = document.getElementById('mediatext-representation-list');
    let index = representationList.querySelectorAll(':scope > .mediatext-representation').length;

    const createMediaFromTemplate = function(type) {
        const mediaHtml = document.getElementById('mediatext-representation-template-' + type)
            .content
            .firstElementChild
            .outerHTML
            .replace(/__index__/g, index++);

        const template = document.createElement('template');
        template.innerHTML = mediaHtml

        return template.content;
    }

    new Sortable(representationList, {
        draggable: '.mediatext-representation',
        handle: '.sortable-handle'
    });

    $('#mediatext-sidebar button').on('click', function(e) {
        const type = this.dataset.ingesterName;
        representationList.append(createMediaFromTemplate(type));
        $('html, body').animate({
            scrollTop: ($('.mediatext-representation-field-wrapper').last().offset().top -100)
        }, 200);
        $('#mediatext-representation-list .no-resources').hide();
    });

    $('#mediatext-representation-list').on('click', 'a.mediatext-remove-new-representation-field', function(e) {
        e.preventDefault();
        $(this).parents(".mediatext-representation-field-wrapper").remove();
        if ($('.mediatext-representation-field-wrapper').length < 1) {
            $('#mediatext-representation-list .no-resources').show();
        }
    });
});
