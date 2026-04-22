@extends('layouts.app')

@section('content')
    <div style="padding: 1.5rem; background: #f8fafc; min-height: 100vh;">

        <!-- Simple Header -->
        <div style="margin-bottom: 1.5rem;">
            <h1 style="margin: 0 0 0.5rem 0; font-size: 24px; font-weight: 700; color: #1e293b;">{{ __('field.org_structure') }}</h1>
            <p style="margin: 0; font-size: 14px; color: #64748b;">{{ __('field.interactive_schema') }}</p>
        </div>

        <!-- Main Card -->
        <div style="background: white; border-radius: 16px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1); overflow: hidden;">

            <!-- Card Header -->
            <div style="padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-bottom: 2px solid #e2e8f0;">
                <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 16px; font-weight: 600; color: #1e293b;">
                    <span style="color: #794DFD;">●</span>
                    {{ __('field.company_structure') }}
                </div>
            </div>

            <!-- Diagram and Inspector Layout -->
            <div style="display: flex; height: calc(100vh - 250px);">
                <!-- Main Diagram Area -->
                <div id="myDiagramDiv" style="flex: 1; background: #f8fafc;"></div>

                <!-- Inspector Sidebar -->
                <div id="inspector" style="width: 300px; background: white; border-left: 2px solid #e2e8f0; overflow-y: auto; padding: 1.5rem;"></div>
            </div>

        </div>
    </div>

    <style>
        /* Inspector Styles */
        .inspector-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .inspector-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #794DFD;
        }

        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .user-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(121, 77, 253, 0.15);
            border-color: #794DFD;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .user-position {
            font-size: 13px;
            color: #64748b;
            font-style: italic;
        }

        .no-users {
            text-align: center;
            padding: 2rem 1rem;
            color: #94a3b8;
            font-size: 14px;
        }

        #myDiagramDiv::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 180px;
            height: 80px;
            background: #f8fafc; /* yoki container bg rangi */
            z-index: 999;
            pointer-events: none;
        }
    </style>

    <script src="{{ asset('/js/go.js') }}"></script>
    <script src="{{ asset('/js/DataInspector.js') }}"></script>

    <script>
        window.trans = {
            chooseDepartment: @json(__('action.choose_department')),
            noEmp: @json(__('field.no_emp_in_dep')),
            department: @json(__('field.department')),
        };

        const light = {
            colors: {
                background: '#f8fafc',
                text: '#1e293b',
                subtext: '#64748b',
                levels: [
                    '#AC193D', '#2672EC', '#8C0095', '#5133AB',
                    '#008299', '#D24726', '#008A00', '#094AB2'
                ]
            },
            fonts: {
                title: '600 1.1rem Arial, sans-serif',
                name: '600 0.95rem Arial, sans-serif',
                normal: '0.875rem Arial, sans-serif',
                italic: 'italic 400 0.85rem Arial, sans-serif'
            }
        };

        function init() {
            const $ = go.GraphObject.make;

            myDiagram = $(go.Diagram, "myDiagramDiv", {
                initialAutoScale: go.Diagram.Uniform,
                "undoManager.isEnabled": true,
                layout: $(go.TreeLayout, { angle: 90, nodeSpacing: 10, layerSpacing: 40 })
            });

            // Node template
            myDiagram.nodeTemplate = $(go.Node, "Auto",
                { deletable: false, click: showUsersInSidebar },
                $(go.Shape, "RoundedRectangle",
                    {
                        stroke: null,
                        parameter1: 10
                    },
                    new go.Binding("fill", "", function (node) {
                        const level = node.part.findTreeLevel();
                        return light.colors.levels[level % light.colors.levels.length];
                    }).ofObject()
                ),
                $(go.Panel, "Table",
                    { padding: 12 },
                    $(go.RowColumnDefinition, { column: 1}),

                    // Department
                    $(go.TextBlock,
                        {
                            row: 0,
                            column: 1,
                            textAlign: "center",
                            alignment: go.Spot.Center,
                            margin: 8,
                            wrap: go.TextBlock.WrapDesiredSize,
                            isMultiline: false
                        },
                        new go.Binding("font", "", function () {
                            return light.fonts.title;
                        }).ofObject(),
                        new go.Binding("stroke", "", function () {
                            return '#ffffff';
                        }).ofObject(),
                        new go.Binding("text", "dept")
                    ),

                    // Picture
                    $(go.Picture,
                        { row: 1, rowSpan: 2, column: 0, width: 50, height: 50, margin: 4 },
                        new go.Binding("source", "pic")
                    ),

                    // Name
                    $(go.TextBlock,
                        { row: 1, column: 2, margin: 4 },
                        new go.Binding("font", "", function () {
                            return light.fonts.name;
                        }).ofObject(),
                        new go.Binding("stroke", "", function () {
                            return '#ffffff';
                        }).ofObject(),
                        new go.Binding("text", "name")
                    ),

                    // Title
                    $(go.TextBlock,
                        { row: 2, column: 2 },
                        new go.Binding("font", "", function () {
                            return light.fonts.italic;
                        }).ofObject(),
                        new go.Binding("stroke", "", function () {
                            return '#ffffff';
                        }).ofObject(),
                        new go.Binding("text", "title", t => t && t.trim() !== "" ? "(" + t + ")" : "")
                    )
                )
            );

            // Link template
            myDiagram.linkTemplate = $(go.Link,
                { routing: go.Link.Orthogonal, corner: 5 },
                $(go.Shape, { strokeWidth: 2, stroke: "#cbd5e1" })
            );

            // Set background
            myDiagram.div.style.backgroundColor = light.colors.background;

            // Load data
            fetch("/org-structure-data")
                .then(response => response.json())
                .then(model => {
                    myDiagram.model = go.Model.fromJson(model);
                    myDiagram.commandHandler.zoomToFit();
                });

            // Clear sidebar on background click
            myDiagram.addDiagramListener("BackgroundSingleClicked", function (e) {
                updateSidebar(null);
            });
        }

        function showUsersInSidebar(e, node) {
            updateSidebar(node.data);
        }

        function updateSidebar(nodeData) {
            const inspector = document.getElementById("inspector");
            inspector.innerHTML = '';

            if (!nodeData) {
                inspector.innerHTML = `<div class="inspector-empty">${window?.trans?.chooseDepartment}</div>`;
                return;
            }

            // Title
            const title = document.createElement('h3');
            title.className = 'inspector-title';
            title.textContent = nodeData.dept || `${window?.trans?.department}`;
            inspector.appendChild(title);

            // Users
            const usersHtml = nodeData.usersHtml || '';

            if (!usersHtml || usersHtml.trim() === '') {
                inspector.innerHTML += `<div class="no-users">${window?.trans?.noEmp}</div>`;
                return;
            }

            // Create temp container to parse HTML
            const temp = document.createElement('div');
            temp.innerHTML = usersHtml;

            const usersList = document.createElement('ul');
            usersList.className = 'user-list';

            const items = temp.getElementsByTagName('li');
            for (let li of items) {
                const text = li.textContent || li.innerText;
                const parts = text.split(' - ');

                const item = document.createElement('li');
                item.className = 'user-item';

                if (parts.length >= 2) {
                    item.innerHTML = `
                        <div class="user-name">${parts[0].trim()}</div>
                        <div class="user-position">${parts.slice(1).join(' - ').trim()}</div>
                    `;
                } else {
                    item.innerHTML = `<div class="user-name">${text}</div>`;
                }

                usersList.appendChild(item);
            }

            inspector.appendChild(usersList);
        }

        window.addEventListener('DOMContentLoaded', init);
    </script>
@endsection
