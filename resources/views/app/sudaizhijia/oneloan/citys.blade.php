<header>
    选择城市</header>
<div class="citys-wrapper">
    <div>
        <!--热门城市hotCity-->
        <div class="hotCity" id="hotCity">
            <h3>热门城市</h3>
            <div class="hotCitylist"></div>
        </div>
        <ul class="slider-content"></ul>
    </div>
</div>
<script>
    var cityListController = {
        init: function() {
            var that = this;
            that.getCityList();
        },
        getCityList: function() {
            /*城市列表数据渲染*/
            $.ajax({
                url: api_sudaizhijia_host + "/v1/location/devices",
                type: "GET",
                dataType: "json",
                data: {},
                success: function(json) {
                    if (json.code == 200 && json.error_code == 0) {
                        cityListController.doCityListView(json.data);
                    }
                }
            })
        },
        /*城市列表数据渲染*/
        doCityListView: function(json) {
            var that = this;
            var data = json;
            var hotCity = "",
                cityList = "";
            $.each(data.hotCity, function(i, b) {
                hotCity += "<span id=" + b.id + " class=" + b.id + ">" + b.name + "</span>";
            })
            $(".hotCitylist").html(hotCity);
            $.each(data.list, function(i, b) {
                cityList += "<li id=" + b.initial + "><h3>" + b.initial + "</h3><ul class='cityList'>";
                $.each(b.citys, function(a, i) {
                    cityList += "<li id=" + i.id + "  class=" + i.id + ">" + i.name + "</li>";
                });
                cityList += "</ul></li>";
            })
            $(".slider-content").html(cityList);
            $('.cityList>li,.hotCitylist>span').off('click').on('click', function() {
                $('#base_info .basic_city').removeClass('error_pro');
                $('#basic-city').parent('div').removeClass('errorStyle');
                var val = $(this).text();
                $('#base_city,.cityVal').val(val);
                global.goBack();
                if (global.cityCallback) {
                    dataController.checkBasicInfo();
                }
            });
        }
    };
    $(function() {
        cityListController.init();
    })

</script>
