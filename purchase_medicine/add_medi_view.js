 $(document).ready(function () {
            // Fetch Salesman List
            $("#filter-form").submit(function (e) {
                e.preventDefault();
                $.post("", $(this).serialize(), function (data) {
                    $("#salesman-content").html(data);
                });
            });

            // Fetch Medicines for selected Salesman
            $(document).on("click", ".view-medicines", function () {
                var salesmanName = $(this).data("name");
                $.post("", { s_name: salesmanName }, function (data) {
                    $("#medicine-content").html(data);
                    $("#salesman-list").addClass("hidden");
                    $("#medicine-details").removeClass("hidden");
                });
            });

            // Go Back to Salesman List
            $("#back-to-salesman").click(function () {
                $("#medicine-details").addClass("hidden");
                $("#salesman-list").removeClass("hidden");
            });
        });