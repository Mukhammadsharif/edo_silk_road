@extends('layouts.app')

@section('content')
    <div style="height: 80vh; display: flex;">
        <div id="myDiagramDiv" style="flex: 1; height: 100%;"></div>
        <div id="inspector" style="width: 200px; padding: 10px;"></div>
    </div>

    <style>
        body { font-family: Arial, sans-serif; }
    </style>

    <script src="{{ asset('/js/go.js') }}"></script>
    <script src="{{ asset('/js/DataInspector.js') }}"></script>


    <script>
        const light = {
            colors: {
                background: '#fff',
                text: '#111827',
                textHighlight: '#11a8cd',
                subtext: '#6b7280',
                badge: '#f0fdf4',
                badgeBorder: '#16a34a33',
                badgeText: '#15803d',
                divider: '#6b7280',
                shadow: '#9ca3af',
                tooltip: '#1f2937',
                levels: [
                    '#AC193D', '#2672EC', '#8C0095', '#5133AB',
                    '#008299', '#D24726', '#008A00', '#094AB2'
                ],
                dragOver: '#f0f9ff',
                link: '#9ca3af',
                div: '#f3f4f6'
            },
            fonts: {
                title: '600 1.2rem InterVariable, sans-serif',
                name: '600 1rem InterVariable, sans-serif',
                normal: '0.875rem InterVariable, sans-serif',
                badge: '500 0.75rem InterVariable, sans-serif',
                link: '600 0.875rem InterVariable, sans-serif',
                italic: 'italic 400 0.875rem InterVariable, sans-serif',
            }
        };

        const dark = {
            colors: {
                background: '#111827',
                text: '#fff',
                subtext: '#d1d5db',
                badge: '#22c55e19',
                badgeBorder: '#22c55e21',
                badgeText: '#4ade80',
                shadow: '#111827',
                dragOver: '#082f49',
                link: '#6b7280',
                div: '#1f2937',
                levels: [
                    '#ff4d4f', '#4da8ff', '#cc33ff', '#7b68ee',
                    '#00b7eb', '#ff7f50', '#32cd32', '#4682b4'
                ]
            },
            fonts: {
                name: '500 0.875rem InterVariable, sans-serif',
                normal: '0.875rem InterVariable, sans-serif',
                badge: '500 0.75rem InterVariable, sans-serif',
                link: '600 0.875rem InterVariable, sans-serif'
            }
        };

        function init() {
            const $ = go.GraphObject.make;

            myDiagram = $(go.Diagram, "myDiagramDiv", {
                initialAutoScale: go.Diagram.Uniform,
                "undoManager.isEnabled": true,
                layout: $(go.TreeLayout, { angle: 90, nodeSpacing: 10, layerSpacing: 30 })
            });

            // Attach ThemeManager
            myDiagram.themeManager = new go.ThemeManager({
                initialTheme: "light",
                themes: {
                    light: {
                        colors: { background: "white" }
                    },
                    dark: {
                        colors: { background: "black" }
                    }
                }
            });

            // Node template with click event
            myDiagram.nodeTemplate = $(go.Node, "Auto",
                { deletable: false, click: showUsersInSidebar },
                new go.Binding("background", "", function (node) {
                    const theme = node.diagram.themeManager.currentTheme;
                    return node.diagram.themeManager.themes[theme].colors.background;
                }).ofObject(),
                $(go.Shape, "Rectangle",
                    { stroke: null },
                    new go.Binding("fill", "", function (node) {
                        const theme = node.diagram.themeManager.currentTheme;
                        const levels = node.diagram.themeManager.themes[theme].colors.levels || ['#0e94df'];
                        const level = node.part.findTreeLevel();
                        return levels[level % levels.length];
                    }).ofObject()
                ),
                $(go.Panel, "Table",
                    { padding: 5 },
                    $(go.RowColumnDefinition, { column: 1}),
                    $(go.TextBlock,
                        {
                            row: 0,
                            column: 1,
                            textAlign: "center",
                            alignment: go.Spot.Center,
                            margin: 5,
                            wrap: go.TextBlock.WrapDesiredSize,
                            isMultiline: false
                        },
                        new go.Binding("font", "", function (node) {
                            return light.fonts.title;
                        }).ofObject(),
                        new go.Binding("stroke", "", function (node) {
                            return light.colors.background;
                        }).ofObject(),
                        new go.Binding("text", "dept")
                    ),
                    $(go.Picture,
                        { row: 1, rowSpan: 2, column: 0, width: 45, height: 45, margin: 4 },
                        new go.Binding("source", "pic")
                    ),
                    $(go.TextBlock,
                        { row: 1, column: 2, margin: 4 },
                        new go.Binding("font", "", function (node) {
                            return light.fonts.name;
                        }).ofObject(),
                        new go.Binding("stroke", "", function (node) {
                            return light.colors.background;
                        }).ofObject(),
                        new go.Binding("text", "name")
                    ),
                    $(go.TextBlock,
                        { row: 2, column: 2 },
                        new go.Binding("font", "", function (node) {
                            return light.fonts.italic;
                        }).ofObject(),
                        new go.Binding("stroke", "", function (node) {
                            return light.colors.background;
                        }).ofObject(),
                        new go.Binding("text", "title", t => t && t.trim() !== "" ? "(" + t + ")" : "")
                    )
                )
            );

            // Context menu (unchanged)
            myDiagram.contextMenu = $(go.Adornment, "Vertical",
                new go.Binding("background", "", function (adorn) {
                    const theme = adorn.diagram.themeManager.currentTheme;
                    return adorn.diagram.themeManager.themes[theme].colors.background;
                }).ofObject(),
            );

            // Set diagram background initially
            myDiagram.div.style.backgroundColor = myDiagram.themeManager.findValue("background");

            // React when theme changes
            myDiagram.themeManager.themeChanged = function () {
                const themeColors = light.colors;
                myDiagram.div.style.backgroundColor = themeColors.background;

                const inspectorEl = document.getElementById("inspector");
                inspectorEl.style.backgroundColor = themeColors.background;
                inspectorEl.style.color = themeColors.text;
            };

            // Load data
            fetch("/org-structure-data")
                .then(response => response.json())
                .then(model => {
                    myDiagram.model = go.Model.fromJson(model);
                    myDiagram.commandHandler.zoomToFit();
                });

            // Clear sidebar when clicking on diagram background
            myDiagram.addDiagramListener("BackgroundSingleClicked", function (e) {
                updateSidebar(null);
            });
        }

        // Function to handle node click and show users in sidebar
        function showUsersInSidebar(e, node) {
            updateSidebar(node.data);
        }

        // Function to update the sidebar content
        function updateSidebar(nodeData) {
            const inspector = document.getElementById("inspector");
            inspector.innerHTML = ''; // Clear previous content

            if (!nodeData) {
                const noSelection = document.createElement('p');
                noSelection.textContent = 'Select a department to view users.';
                noSelection.style.padding = '10px';
                noSelection.style.font = light.fonts.normal;
                noSelection.style.color = light.colors.subtext;
                inspector.appendChild(noSelection);
                return;
            }

            // Create a title for the department
            const title = document.createElement('h3');
            title.textContent = nodeData.dept || 'Department';
            title.style.margin = '0 0 10px 0';
            title.style.font = light.fonts.title;
            title.style.color = light.colors.text;
            inspector.appendChild(title);

            // Use usersHtml if available, otherwise show no users message
            const usersHtml = nodeData.usersHtml || '';
            if (!usersHtml) {
                const noUsers = document.createElement('p');
                noUsers.textContent = 'No users in this department.';
                noUsers.style.font = light.fonts.normal;
                noUsers.style.color = light.colors.subtext;
                inspector.appendChild(noUsers);
                return;
            }

            // Create a container for the users HTML
            const ul = document.createElement('div');
            ul.style.listStyle = 'none';
            ul.style.padding = '0';
            ul.style.margin = '0';
            ul.innerHTML = usersHtml; // Inject the pre-formatted HTML

            // Apply theme styles to list items
            const listItems = ul.getElementsByTagName('li');
            for (let li of listItems) {
                li.style.padding = '5px 0';
                li.style.font = light.fonts.normal;
                li.style.color = light.colors.text;
            }

            inspector.appendChild(ul);
        }

        window.addEventListener('DOMContentLoaded', init);
    </script>
@endsection
