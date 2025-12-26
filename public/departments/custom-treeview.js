$.fn.extend({
    treed: function (o) {
        var openedClass = 'ik-minus';
        var closedClass = 'ik-plus';
        var storageKey = 'openBranches'; // Key for local storage

        if (typeof o != 'undefined') {
            if (typeof o.openedClass != 'undefined') {
                openedClass = o.openedClass;
            }
            if (typeof o.closedClass != 'undefined') {
                closedClass = o.closedClass;
            }
        }

        // Initialize each of the top levels
        var tree = $(this);
        tree.addClass("tree");
        tree.find('li').has("ul").each(function () {
            var branch = $(this); // li with children ul
            branch.prepend("<i class='ik " + closedClass + "'></i>");
            branch.addClass('branch');
            branch.on('click', function (e) {
                if (this == e.target) {
                    var icon = $(this).children('i:first');

                    // Check if the clicked branch is already open
                    var isOpen = icon.hasClass(openedClass);

                    // Close all other branches
                    tree.find('.branch .ik.' + openedClass).each(function () {
                        var otherBranch = $(this).closest('li');
                        otherBranch.children().children().hide(); // Hide the children
                        $(this).removeClass(openedClass).addClass(closedClass); // Change the icon
                    });

                    // If it was not open, open the clicked branch
                    if (!isOpen) {
                        icon.toggleClass(openedClass + " " + closedClass);
                        $(this).children().children().toggle();
                    }

                    // Save the opened/closed state
                    saveBranchState();
                }
            });
            branch.children().children().toggle(); // Ensure children are toggled initially
        });

        // Restore state from local storage
        restoreBranchState();

        // Save the state of opened branches
        function saveBranchState() {
            var openBranches = tree.find('.branch .ik.' + openedClass).closest('li').map(function() {
                return $(this).index(); // Use index to identify branches
            }).get();
            localStorage.setItem(storageKey, JSON.stringify(openBranches));
        }

        // Restore the state of branches
        function restoreBranchState() {
            var openBranches = JSON.parse(localStorage.getItem(storageKey)) || [];
            tree.find('li').has("ul").each(function () {
                var branch = $(this);
                if (openBranches.includes(branch.index())) {
                    var icon = branch.children('i:first');
                    icon.removeClass(closedClass).addClass(openedClass);
                    branch.children().children().show();
                }
            });
        }

        // Fire event from the dynamically added icon
        tree.find('.branch .indicator').each(function(){
            $(this).on('click', function () {
                $(this).closest('li').click();
            });
        });

        // Fire event to open branch if the li contains an anchor instead of text
        tree.find('.branch>a').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });

        // Fire event to open branch if the li contains a button instead of text
        tree.find('.branch>button').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
    }
});

// Initialization of treeviews
$('#tree3').treed();
$('#tree4').treed();
