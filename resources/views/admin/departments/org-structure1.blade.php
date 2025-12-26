@extends('layouts.app')

@section('content')

<div style="height: 90vh" id="tree"></div>

<style>
    .orgchart .node text {
        text-align: center;
        font-size: 14px;
    }
    #boc-avatar {
        display: none !important;
    }
</style>

<script src="{{ asset('/js/orgchart.js') }}"></script>
<script>
    let chart = null

    fetch("/org-structure-data")
        .then(response => response.json())
        .then(data => {
            chart = new OrgChart("#tree", {
                template: "ana",
                nodeBinding: {
                    field_0: "name",
                    field_1: "first_user",
                    field_2: "first_user_position",
                    img_0: "img"
                },
                nodes: data,
                toolbar: {
                    layout: true,
                    zoom: true,
                    fit: true,
                    expandAll: false,
                    fullScreen: true
                },
                editForm: {
                    buttons: {
                        edit: null
                    },
                    generateElementsFromFields: false,
                    elements: [
                        { type: 'myTextArea', label: 'Description:', binding: 'users' }
                    ]
                },
            });
        });

    OrgChart.SEARCH_PLACEHOLDER = "{{ __('action.search') }}";
//     OrgChart.templates.ana.node = `
//     <rect x="0" y="0" height="100" width="240" fill="#edd180" stroke-width="1" stroke="#edd180" rx="10" ry="10"></rect>
// `;
    // OrgChart.templates.ana.field_0 = '<text data-width="230" data-text-overflow="multiline-4-ellipsis" style="font-size: 13px;" fill="#fff" x="115" y="28" text-anchor="middle">{val}</text>';
    OrgChart.templates.ana.field_0 = `
    <text data-width="150" data-text-overflow="multiline-4-ellipsis" x="120" y="28" text-anchor="middle" style="font-size: 15px;" fill="#fff">{val}</text>`;

    OrgChart.templates.ana.field_1 = `
    <text data-width="150" data-text-overflow="multiline-4-ellipsis" x="120" y="68" text-anchor="middle" style="font-size: 15px;" fill="#fff">{val}</text>`;

    OrgChart.templates.ana.field_2 = `
    <text data-width="150" data-text-overflow="multiline-4-ellipsis" x="120" y="98" text-anchor="middle" style="font-size: 13px; font-style: italic" fill="#fff">{val}</text>`;

    OrgChart.templates.ana.img_0 = `
    <image x="10" y="10" width="40" height="40" preserveAspectRatio="xMidYMid slice" xlink:href="{val}" style="border-radius: 50%; border: 2px solid #fff;"></image>
`;

    OrgChart.elements.myTextArea = function (data, editElement, minWidth, readOnly) {
        var id = OrgChart.elements.generateId();
        var value = data[editElement.binding];
        if (value === undefined) value = '';
        if (readOnly && !value) {
            return {
                html: ''
            };
        }
        var rOnlyAttr = readOnly ? 'readonly' : '';
        var rDisabledAttr = readOnly ? 'disabled' : '';
        return {
            // html: `<label for="${id}">${editElement.label}</label>
            //           <textarea ${rDisabledAttr} ${rOnlyAttr} id="${id}" name="${id}" style="width: 100%;height: 100px;" data-binding="${editElement.binding}">${value}</textarea>`,
            html: `<span id="${id}" style="width: 100%;height: 100px;" data-binding="${editElement.binding}">${value}</span>`,
            id: id,
            value: value
        };

    };

</script>
@endsection
