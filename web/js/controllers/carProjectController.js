
/* основные настройки фронтэнда */

var uiOptions = {};

uiOptions.backendUrl = 'http://192.168.56.248/api/';
// uiOptions.backendUrl = 'http://192.168.10.248/api/';

uiOptions.DEFAULT_PAGE = '';	            //главная страница
// uiOptions.DEFAULT_PAGE = 'actionOptions';	//настройки и справочники

/* основные настройки фронтэнда */


// переводы для datatables
var dtLanguage = 
{
  "processing": "Подождите...",
  "search": "Поиск:",
  "lengthMenu": "Показать _MENU_ записей",
  "info": "Записи с _START_ до _END_ из _TOTAL_ записей",
  "infoEmpty": "Записи с 0 до 0 из 0 записей",
  "infoFiltered": "(отфильтровано из _MAX_ записей)",
  "infoPostFix": "",
  "loadingRecords": "Загрузка записей...",
  "zeroRecords": "Записи отсутствуют.",
  "emptyTable": "Нет данных",
  "paginate": {
    "first": "<<",
    "previous": "<",
    "next": ">",
    "last": ">>"
  },
  "aria": {
    "sortAscending": ": активировать для сортировки столбца по возрастанию",
    "sortDescending": ": активировать для сортировки столбца по убыванию"
  }
};



var carProjectApp = angular.module('carProjectApp', ['datatables', 'angular-growl']);

carProjectApp.config(['growlProvider', function(growlProvider) { growlProvider.globalTimeToLive(3000); }]);

    angular.module('carProjectApp').factory('HTTP',  function($http, growl){

        var request_url = uiOptions.backendUrl;

	    return{
                request_actions: function(obj, method='get')
                {
                    var config = 
                    {
                        headers: { 
                            "X-HTTP-Method-Override": "POST",
                            "Accept": "application/json",
                            "Content-Type" : "application/x-www-form-urlencoded;charset=utf-8"
                        }
                    };

                    switch (method){

                        case 'delete':
                                    var url =request_url+obj.action+'/'+obj.data;

                                    return $http.delete(url, {id:obj.data });
                                    break;

                        case 'post':  
                                    return $http.post(request_url+obj.action, obj, config);
                                    break;

                        case 'put':  
                                    return $http.put(request_url+obj.action+'/'+obj.id, obj, config);
                                    break;

                        default:   
                                    // return $http.get(request_url+obj.action); 
                                    return $http.get(request_url+obj.action).then(function (results) {
                                              return results;
                                          }, function (error) {
                                            getError(error)
                                          });
                                    break
                    }

                },
        }

        function getError(error) {

            if (error.status == 0) {
                var errorMsg = '<h4>500: Internal Server Error</h4>'+
                             '<p>Внутренняя ошибка сервера</p>';
                  growl.error(errorMsg, {ttl: -1});
            } else {
                var errorMsg = '<h4>'+error.status+': '+error.statusText+'</h4>'+
                             '<p>'+error.data+'</p>';
                growl.error(errorMsg, {ttl: -1});
            };
            console.log( errorMsg );
        }

    // }).controller('AutoCtrl',  function( $log, $q, HTTP, $scope, $compile, DTOptionsBuilder, DTColumnBuilder, $rootScope){
    }).controller('AutoCtrl',  ['$log', '$q', 'HTTP', '$scope', '$compile', 'DTOptionsBuilder', 'DTColumnBuilder', '$rootScope', 'growl', function( $log, $q, HTTP, $scope, $compile, DTOptionsBuilder, DTColumnBuilder, $rootScope, growl){
   
//переменные для приложения  
    var vm = this;
    vm.current = {}
    vm.refills = {};
    $scope.statics = {};    //для данных отражающих статистику чего либо
    $scope.statics.refills = {};
    // $scope.active = DEFAULT_PAGE_TITLE;   //выделяет пункт меню по умолчанию
//переменные для приложения  

//фильтры
    $scope.filter = {};
    $scope.filter.cars = [];
    $scope.filter.fuels = [];
    $scope.filter.azss = [];
//фильтры

//формы
    $scope.actionRefills = false;       //все заправки
    $scope.actionRefuel = false;       //новая заправка
    $scope.actionEditRefuel = false;   //редактирование заправки
    $scope.actionOptions = false;      //настройки 
//формы

//запускает ui приложение
    startAppRouter();


    var obj = {};   //для работы со значениями сущностей и др временными данными

    // vm.reloadTable = reloadTable;
    // vm.dtInstance = {};

//прячет все формы
    function hideAllForms()
    {
        $scope.actionRefills = false;
        $scope.actionRefuel = false;
        $scope.actionOptions = false;
        $scope.actionEditRefuel = false;
    }


    function startAppRouter()
    {

        // console.log( '- - - - - startAppRouter' );

        getAllProperties(true); //получает свойства сущностей, настройки по умолчанию.
        return true;    //?
    }
    

//управляет формами
    $scope.formsRouter = formsRouter;
    /*
      id- по умолчанию открывается таблица всех заправок
        clearInput если true тогда отображаемая форма чистится, если нет то данные остаются, нужно для добавления новой или редактирования заправки
    */
    function formsRouter(id=false, clearInput=true)
    {
        hideAllForms();

        getAllProperties();

        switch (id)
        {
            case 'actionRefuel':   
                                    $scope.active='refuel';

                                    $scope.refillForm = {}; //очищаем форму
                                    $scope.actionEditRefuel = false;    //дизаблим редактирование
                                    $scope.actionRefuel = true; 

                                    getLastFuelPrice(); //достаем последнюю цену заправки

                                    showRefill();
                                    break;

            case 'actionEditRefuel':
                                    //дизаблим выбор машины
                                    $scope.actionEditRefuel = true;
                                    $scope.actionRefuel = true; 

                                    showRefill();
                                    break;
            case 'actionOptions':
                                   $scope.active='options';

                                   $scope.optionsForm = {}; //объект для настроек, сущности по умолчанию 
                                   $scope.actionOptions = true;
                                   $scope.actionEditRefuel = false;    //энаблим выбор машины

                                   hideOptionsForm();   //скрыть формы которые присутствуют только на странице с настройками
                                   showOptions();
                                   break;

            default:    $scope.active='home';   //title по умолчанию
                        $scope.actionRefills = true;    //отображает страницу с заправками
                        
                        $scope.dataIsReceiving = true;  //показывает крутилку получения данных

// console.log( vm);

                        //параметры для запроса по которому вытаскиваются данные при запуске приложения исходя из настроек по умолчанию, которые получается ранее с бэкэнда
                        var defaultFilter = {};
                        defaultFilter.cars = [$scope.defaultValues.car]; //машина по умолчанию
                        $scope.filter.cars[0] = {'id': $scope.defaultValues.car};    //выделяет в SELECT фильтре машину по умолчанию

                        // console.log(vm.dtInstance.reloadData);
                        // console.log( '- - - zx');

                        // if (vm.dtInstance.reloadData == undefined) {
                        //     preLoadDataTableJobs( false, defaultFilter );    // старт загрузки данных для таблицы всех заправок
                        // } else {
                        //     preLoadDataTableJobs( true, defaultFilter );    // старт загрузки данных для таблицы всех заправок
                        // };
                            preLoadDataTableJobs( false, defaultFilter );    // старт загрузки данных для таблицы всех заправок

                        break

        }

    }


// получаем объекты, все машины, все заправки, все бензины, значения по умолчанию
    function getAllProperties(startApp=false)
    {
        var obj = {}; 

        //все машины
        var allCars = function()
        { 
            obj.action = 'cars';  

            return HTTP.request_actions(obj)
        }

        //все заправки
        var allAzs = function()
        {
            obj.action = 'azs';  

            return   HTTP.request_actions(obj)
        }

        //все бензины
        var allFuels = function()
        {
            obj.action = 'fuels';  

            return   HTTP.request_actions(obj)
        } 

        //значениея по умолчанию
        var defaultValues = function()
        {
            obj.action = 'options';  

            return   HTTP.request_actions(obj)
        }       

        //после того как данные получины раскладывем все это по переменным
        $q.all([allCars(), allAzs(), allFuels(), defaultValues()]).then(function(results) {
           $scope.allCars = results[0].data;

           $scope.allAzs = results[1].data;
           $scope.allFuels = results[2].data;
           $scope.defaultValues = results[3].data;

console.log($scope.allAzs);

           //копии для использования в настройках и справочниках
           $scope.carsList =   angular.copy($scope.allCars);
           $scope.azsList  =   angular.copy($scope.allAzs);
           $scope.fuelsList=   angular.copy($scope.allFuels);

            if( startApp)
            {
                formsRouter( uiOptions.DEFAULT_PAGE);
            }

        });
    }


    //получает данные по какой либо сущности
    function getOneInstance(instance, id)
    {
        var obj = {};

        var findOne = function()
        { 
            obj.action = instance+'/'+id; 
            var method = 'get';

            return HTTP.request_actions(obj, method)
        }

        return $q.all([findOne()]).then(function(results) {
            
            $scope.optionsForm.oneInstance = results[0].data;
        });

    }


//получает сумму затраченных денег по выбранной заправке либо по выбранному бензину
    function getSummFromRefills(instance, id)
    {
        var obj = {};

        var findOne = function()
        { 
            obj.action = 'refills/'+instance+'/'+id;
            var method = 'get';

            return HTTP.request_actions(obj, method)
        }

        return $q.all([findOne()]).then(function(results) {
            $scope.optionsForm.summFromRefills = results[0].data;

        });
    }


    //получает стоимость бензина при последней заправке, используется для автоподстановки стоимости бензина при добавлении новой заправки 
    function getLastFuelPrice()
    {      
        var lastFuelPrice = function()
        {
            obj.action = 'refills/lastrefill';  

            return   HTTP.request_actions(obj)
        }

        $q.all([lastFuelPrice()]).then(function(results) {
            $scope.lastFuelPrice = results[0].data.price_litr;
            $scope.refillForm.price_litr = results[0].data.price_litr;
        });
        
    }    


    $scope.dataIsReceiving = true;  //получение данных началось
    $scope.is_update = false;       //это первый старт таблице, не обновление


    // vm.refills.dtOptions = [];
    // vm.refills.dtColumns = [];
    // vm.refills.dtInstance = [];

    // vm.refills.refillsData = {};
    // preLoadDataTableJobs(false, false); // Старт прелоадера данных для таблицы всех заправок


//получает данные по заправкам, которые выводятся в таблицу
    function preLoadDataTableJobs(is_update, data=false ){
    
        //получаем данные по заправкам с бэкэнда, в гет параметре передаем то что выбрано в фильтрах 
        var refillsData = function (){
            var obj = {};

            if(data){
                obj.action = 'refills/?params='+JSON.stringify(data);
            }else{
                obj.action = 'refills';
            }

            return HTTP.request_actions(obj).then(function(data){

                return data;
          }, failCallbacks);
        };

        // Ошибка получения данных из модели
        function failCallbacks(d){
          console.log(d);
        };


        //дожидается получения данных из запроса refillsData()
        $q.all([refillsData()])
        .then(function(results) {

            //если данные пришли
            if (results[0])
            {
                //узнаем тип данных в ответе
                var responseData = typeof(results[0].data);
                //если тип данных объект тогда все ок,
                // иначе значит была ошибка формирования данныз на бэкэнде
                if ( responseData=='object' )
                {
                    vm.refills.refillsData = results[0].data;

                    //считает общее количество заправок, потраченных денег и сколько литров бензина залито
                    $scope.statics.refills.totalCount = vm.refills.refillsData.length;

                    var litr = 0;
                    var summ = 0;

                // if ( vm.refills.refillsData)
// console.log( vm.refills.refillsData);                
                    vm.refills.refillsData.forEach(function(tableRow){
                        litr += parseInt( tableRow.flooded_litr );
                        summ += parseInt( tableRow.sum_refill );
                    })

                    $scope.statics.refills.flooded_litr = litr;   
                    $scope.statics.refills.sum_refill = summ;   

                }else{

                    vm.refills.refillsData = [];
                    $scope.statics.refills.totalCount = 0;
                    $scope.statics.refills.flooded_litr = 0;
                    $scope.statics.refills.sum_refill = 0;
                }
            }
        }).then(function(d) {
/*q*/

            if(!is_update){
                refillsTable();
            }else{
                refillsTable();
                // $scope.refills_area.dtInstance.reloadData(callback, false);
                // vm.dtInstance.reloadData(callback, false);
                
        // console.log( $scope.refills_area.dtInstance);
        // console.log( vm.dtInstance);
                // vm.dtInstance.reloadData(null, true);
            }
            $scope.dataIsReceiving = false;
        });
    }


    //постороение таблицы по полученным заправкам через dataTables
    function refillsTable(){

        // получает данные для постороения таблице через промис
        var getDataPromise = function() 
        {
            var deferred = $q.defer();        
            deferred.resolve( vm.refills.refillsData );

           return deferred.promise;
        };

        vm.dtColumns = 
        [
            DTColumnBuilder
                .newColumn('car_id')
                .withClass('font_no_mono text-center')
                .withTitle('Машина'),
            DTColumnBuilder
                .newColumn('data')
                .withClass('font_no_mono text-center')
                .withTitle("Дата<br>заправки")
                .withOption('width', '120px'),
            DTColumnBuilder
                .newColumn('azs_id')
                .withClass('font_no_mono text-left')
                .withTitle('Заправка')
                .withOption('width', '260px'),
            DTColumnBuilder
                .newColumn('fuel_id')
                .withClass('font_no_mono text-center')
                .withTitle('Бензин')
                .withOption('width', '100px'),
            DTColumnBuilder
                .newColumn('flooded_litr')
                .withClass('font_no_mono text-center')
                .withTitle('Заправлено<br>(л)'),
            DTColumnBuilder
                .newColumn('price_litr')
                .withClass('font_no_mono text-center')
                .withTitle('Цена<br>за литр'),
            DTColumnBuilder
                .newColumn('sum_refill')
                .withClass('font_no_mono text-center')
                .withTitle('Сумма<br>заправки'),
            DTColumnBuilder
                .newColumn(null)
                .withClass('font_no_mono text-center')
                .withTitle('Действия')
                .withOption('width', '120px')
                .renderWith(cellActions),
        ];

        vm.dtOptions = DTOptionsBuilder.fromFnPromise(getDataPromise())
            .withOption('paging', (vm.refills.refillsData.length > 10) ? true : false)
            .withOption('rowCallback', function(row, data, dataIndex) {
                            $compile(row)($scope);
                        })
            .withPaginationType('full_numbers')
            .withOption('stateSave', false)
            .withOption('info', false)
            .withLanguage(dtLanguage)
            .withOption('order', [0, 'desc'])
            .withOption('searching', false)
            // .withOption('searching', (vm.refills.refillsData.length > 10) ? true : false)
            .withDisplayLength(10)
            .withOption("lengthMenu", [[10, 25, 50, 100, 200, 500, -1], [10, 25, 50, 100, 200, 500, "All"]])
            .withOption('bAutoWidth', false)
            // .withOption("lengthChange", false)
            ;

        vm.dtInstance = {};

    // var zx = getDataPromise()
    // console.log( vm.refills.dtOptions ); 
    // console.log( $scope.refills_area.dtInstance ); 

    }


    //перезагружает таблицу
    function reloadTable() {
        var resetPaging = true;
        vm.dtInstance.reloadData(callback, resetPaging);
console.log('---');
    }

    function callback(qwe) {
       
    }

    

    function reloadTableRefills()
    {

        preLoadDataTableJobs(true);
    }

    
    $scope.refillForm = {}; //объект для параметров заправки        //?


//отображает форму добавления новой заправки
    function showRefill()
    {

        moment.lang('ru');
        $scope.refillForm.date = moment().format('YYYY-MM-DD');

        $scope.refillForm.selectedCar   = {id: $scope.defaultValues.car};
        $scope.refillForm.selectedFuel  = {id: $scope.defaultValues.fuel};
        $scope.refillForm.selectedAzs   = {id: $scope.defaultValues.azs};

        //ид сущности по умолчанию нам известно из конфиг файла, достаем имя, для этого в функцию getInstanceName передается объект где 
        //  будет производится поиск и ид сущности имя которой нам нужно узнать
        // $scope.refillForm.selectedCar   = {id: $scope.defaultValues.car,  name: getInstanceName($scope.allCars, $scope.defaultValues.car)};
        // $scope.refillForm.selectedFuel  = {id: $scope.defaultValues.fuel, name: getInstanceName($scope.allFuels, $scope.defaultValues.fuel)};
        // $scope.refillForm.selectedAzs   = {id: $scope.defaultValues.azs,  name: getInstanceName($scope.allAzs, $scope.defaultValues.azs)};
        
    }


//отображает форму редактирования настроек
    function showOptions()
    {
         //выводит в selected сущность по умолчанию (значение задано в файле конфигурации по умолчанию)
        $scope.optionsForm.selectedCar   = {id: $scope.defaultValues.car};
        $scope.optionsForm.selectedFuel  = {id: $scope.defaultValues.fuel};
        $scope.optionsForm.selectedAzs   = {id: $scope.defaultValues.azs};

        // $scope.optionsForm.selectedCar   = {id: defaultCar,  name: getInstanceName($scope.allCars, defaultCar)};
        // $scope.optionsForm.selectedFuel  = {id: defaultFuel, name: getInstanceName($scope.allFuels, defaultFuel)};
        // $scope.optionsForm.selectedAzs   = {id: defaultAzs,  name: getInstanceName($scope.allAzs, defaultAzs)};

    }


    //достает имя сущности из массива по ид
    function getInstanceName(arr, id)
    {
        var name;
        if (typeof(arr) ==='object')
        {
            arr.forEach(function(entry){
                if(id == entry.id)
                {
                    name = entry.name;    
                }
            })
        }
        return name;
    }


// сохраняет заправку   
    $scope.saveRefill = saveRefill;
    function saveRefill(refillForm)
    {
        var obj = {};
        obj.car_id = refillForm.selectedCar.id;
        obj.data = refillForm.date;
        obj.fuel_id = refillForm.selectedFuel.id;
        obj.price_litr = refillForm.price_litr;
        obj.flooded_litr = refillForm.zalito;
        obj.sum_refill = refillForm.summa;
        obj.azs_id = refillForm.selectedAzs.id;
        if (refillForm.comment){
            obj.comment = refillForm.comment
        }

        obj.action = 'refills';
        var method = 'post';

        HTTP.request_actions(obj, method)
        .success(function(data) {
            //обновляем таблицу

            $scope.dataIsReceiving = true;
            preLoadDataTableJobs( true );

            formsRouter();
            getAllProperties();

            growl.success( 'Заправка добавлена');
        }).error(function(msg, code) {
            console.log('error');
        });

        $scope.refillForm={};
        // hideAddForm();
    }

// сохраняет изменения в заправке   
    $scope.saveEditedRefill = saveEditedRefill;
    function saveEditedRefill(refillForm)
    {
        var obj = {};
        obj.car_id = refillForm.selectedCar.id;
        obj.data = refillForm.date;
        obj.fuel_id = refillForm.selectedFuel.id;
        obj.price_litr = refillForm.price_litr;
        obj.flooded_litr = refillForm.zalito;
        obj.sum_refill = refillForm.summa;
        obj.azs_id = refillForm.selectedAzs.id;
        if (refillForm.comment){
            obj.comment = refillForm.comment
        }

        obj.id = $scope.refillForm.refill_id;   //ид заправки
            console.log(obj.id);
        obj.action = 'refills';
        var method = 'put';

        HTTP.request_actions(obj, method)
        .success(function(data) {
            //обновляем таблицу

            $scope.dataIsReceiving = true;
            preLoadDataTableJobs( true );

            formsRouter();
            getAllProperties();

            growl.success("Изменения сохранены." );
        }).error(function(msg, code) {
            console.log('error');
        });


        // refillForm={};
        $scope.refillForm={};
        // hideAddForm();
    }

//вычисляет сумму заправки
    $scope.calculateSumm = calculateSumm;
    function calculateSumm()
    {
        $scope.refillForm.summa = ($scope.refillForm.price_litr * $scope.refillForm.zalito).toFixed(2);
    }

//выводит кнопки действий
    function cellActions(data, type, full, meta)
    {
        vm.refills[data.id] = data;
        // console.log(data);
        return  '<a type="button" class="icon-wrap iw-1x" ng-click="editRefill(\''+data.id+'\')"><span class="fa fa-pencil-square-o text-info " title="Редактировать заправку"></span></a>'
        // return  '<a type="button" class="icon-wrap iw-1x" noclick="editRefill(\''+data.id+'\')"><span class="fa fa-pencil-square-o text-info " title="Редактировать заправку"></span></a>'
               // +'<a type="button" class="icon-wrap iw-1x"  ng-click="removeRefill(\''+data.id+'\')"><span class="fa fa-trash-o text-danger" title="Удалить заправку"></span></a>'
               +'&nbsp;&nbsp;<a type="button" class="icon-wrap iw-1x" id="refill_remove_'+data.id+'" onclick="removeRefill(\'refill_remove_'+data.id+'\',\''+data.id+'\')"><span class="fa fa-trash-o text-danger" title="Удалить заправку"></span></a>'
               // +'<button ng-click="test()">zx</button>'
               ;
    }

    $scope.test = test;
    function test()
    {
        // // $scope.filter = {};
        // $scope.filter.cars.allcars = {'id':0, 'name':'all'};
        // console.log($scope.filter.cars);
    }

// ! исправить косяк, не проставляется тип бензина и заправка при редактировании заправки
//редактировать заправку
    $scope.editRefill = editRefill;
    function editRefill(id)
    {
        var refillObj = $scope.refills_area.refills[id];

// console.log( refillObj.fuel_id );



        $scope.refillForm.refill_id = id;   //ид текущей заправки
        $scope.refillForm.selectedCar = refillObj.car_id;
        $scope.refillForm.date = refillObj.data;

        $scope.refillForm.selectedFuel = refillObj.fuel_id;
        // $scope.refillForm.selectedFuel = refillObj.fuel_id;
// $scope.refillForm.selectedFuel = 6;


        $scope.refillForm.price_litr = refillObj.price_litr;
        $scope.refillForm.zalito = refillObj.flooded_litr;
        $scope.refillForm.summa = refillObj.sum_refill;
        $scope.refillForm.selectedAzs = refillObj.azs_id;
        if (refillObj.comment){
            $scope.refillForm.comment = refillObj.comment;
        }
        formsRouter('actionEditRefuel', false);

    }


 //удаляет заправку 
    $scope.removeRefill = removeRefill;
    function removeRefill(id)
    {
        obj.action = 'refills';
        obj.data = id;
        var method = 'delete';

        if (confirm('Подтверждаете удаление заправки c id='+obj.data+'?')) 
        {
            HTTP.request_actions(obj, method)
            .success(function(data) {
                //обновляем таблицу
                $log.log(data);

                $scope.dataIsReceiving = true;
                preLoadDataTableJobs( true );

                growl.success( 'Заправка удалена' );

                // reloadTableRefills();
                // formsRouter();
            }).error(function(msg, code) {
                console.log('error');
            });
        }

    }


//сохраняет настройки
    $scope.saveOptions = saveOptions;
    function saveOptions(optionsForm)
    {

        var obj = {};

        if (optionsForm.selectedCar){
            obj.car = optionsForm.selectedCar.id;  
        }
        if (optionsForm.selectedFuel){
            obj.fuel = optionsForm.selectedFuel.id;
        }
        if (optionsForm.selectedAzs){
            obj.azs  = optionsForm.selectedAzs.id;
        }

        obj.action = 'options';
        var method = 'put';

        HTTP.request_actions(obj, method)
        .success(function(data) {
            //обновляем данные по умолчанию
            getAllProperties();      
            // reloadTable();
            // formsRouter();

            growl.success( 'Настройки приложения "по умолчанию" сохранены');

        }).error(function(msg, code) {
            console.log('error');
        });

        $scope.obj = {};
        // formsRouter();

    } 


//отмена добавление\редактирования заправки
    $scope.cancelRefill = cancelRefill;
    function cancelRefill()
    {
        formsRouter();

        growl.warning( 'Отменено', {ttl: 1000});
    }


//отмена в настройках
    $scope.cancelOptions = cancelOptions;
    function cancelOptions()
    {
        showOptions();

        growl.warning( 'Изменения отменены', {ttl: 2000});
    }


//пряет формы в настройках и справочниках
    $scope.hideOptionsForm = hideOptionsForm;
    function hideOptionsForm(button_pressed=false)
    {
        $scope.options.car.new=false;
        $scope.options.azs.new=false;
        $scope.options.fuel.new=false;
        $scope.options.car.edit=false;
        $scope.options.azs.edit=false;
        $scope.options.fuel.edit=false;

        $scope.options.car.info = false;
        $scope.options.fuel.info = false;
        $scope.options.azs.info = false;

        if ( button_pressed ){
            growl.warning( 'Отменено', {ttl: 1000});
        }
    }

//объекты для использования в настройках и справочниках
    $scope.options={}; 
    $scope.options.car={}; 
    $scope.options.azs={}; 
    $scope.options.fuel={}; 


//управляет показом форм ввода или отображения 
    $scope.showhideOptionsForm = showhideOptionsForm;
    function showhideOptionsForm (formId)
    {
        hideOptionsForm();

        switch (formId)
        {
            case 'car' : 
                            $scope.options.car.new=true;
                            $scope.optionsForm.oneInstance = '';
                            break;

            case 'carEdit' :
                            $scope.options.car.edit=true;
                            break;

            case 'azs' : 
                            $scope.options.azs.new=true;
                            $scope.optionsForm.oneInstance = '';
                            break;

            case 'azsEdit' : 
                            $scope.options.azs.edit=true;
                            break;

            case 'fuel' : 
                            $scope.options.fuel.new=true;
                            $scope.optionsForm.fuelList = '';
                            break;

            case 'fuelEdit' : 
                            $scope.options.fuel.edit=true;
                        break;

        }
    }


    $scope.removeFromOptions = removeFromOptions;
    function removeFromOptions (formId, data)
    {
        switch (formId)
        {
            case 'car' : 
                        var obj = {};
                        obj.name = data.name;

                        obj.action = 'cars/'+data.id;
                        var method = 'delete';

                        if (confirm('Подтверждаете удаление '+obj.name+'?')) 
                        {
                            HTTP.request_actions(obj, method)
                            .success(function(data) {

                                //Обновляем добавленные данные 
                                getAllProperties();

                                growl.success( 'Машина удалена');
                                
                            }).error(function(msg, code) {
                                console.log('error');
                            });
                        }

                        break;

            case 'fuel' :
                        var obj = {};
                        obj.name = data.name;
                        obj.action = 'fuels/'+data.id;
                        var method = 'delete';

                        if (confirm('Подтверждаете удаление '+obj.name+'?')) 
                        {
                            HTTP.request_actions(obj, method)
                            .success(function(data) {

                                //Обновляем добавленные данные 
                                getAllProperties();

                                growl.success( 'Топливо добавлено');
                                
                            }).error(function(msg, code) {
                                console.log('error');
                            });
                        }

                        break;

            case 'azs' : 
                        var obj = {};
                        obj.name = data.name;

                        obj.action = 'azs/'+data.id;
                        var method = 'delete';

                        if (confirm('Подтверждаете удаление '+obj.name+'?')) 
                        {
                            HTTP.request_actions(obj, method)
                            .success(function(data) {

                                //Обновляем добавленные данные 
                                getAllProperties();

                                growl.success( 'АЗС добавлена');
                                
                            }).error(function(msg, code) {
                                console.log('error');
                            });
                        }

                        break;
        }
        hideOptionsForm();

    }



// объект выбранного параметра, машина или заправка или бензин
    $scope.optionsOneInstance = optionsOneInstance;
    function optionsOneInstance (formId, data) //showhideOptionsInfo
    {
        // $scope.oneInstance = {};
        showhideOptionsForm();
        switch (formId)
        {
            case 'car' :
                        $scope.options.car.info = true;
                        getOneInstance('cars', data.id)

                        break;
            case 'azs' :
                        $scope.options.azs.info = true;
                        getOneInstance('azs', data.id);
                        getSummFromRefills('azs', data.id);

                        break;
            case 'fuel' :
                        $scope.options.fuel.info = true;
                        getOneInstance('fuels', data.id);
                        getSummFromRefills('fuel', data.id);
                        break;

        }
    }


//добавление нового объекта из options 
    $scope.newFromOptions = newFromOptions;
    function newFromOptions(formId, data)
    {
        switch (formId)
        {
            case 'car' : 
                        data.action = 'cars';

                        var method = 'post';
                        HTTP.request_actions(data, method)
                        .success(function(data) {
                            
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'Машина добавлена');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });

                        break;

            case 'carEdit' : 
                        data.action = 'cars';

                        var method = 'put';

                        HTTP.request_actions(data, method)
                        .success(function(data) {
                            
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'Параметры машины изменены');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });

                        break;

            case 'fuel' :
                        var obj = {};
                        obj.name = data.name;

                        obj.action = 'fuels';
                        var method = 'post';

                        HTTP.request_actions(obj, method)
                        .success(function(data) {
                            
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'Топливо добавлено');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });
                        break;

            case 'fuelEdit' : 
                        var obj = {};
                        obj.action = 'fuels';
                        obj.name = data.name;
                        obj.id = data.id;

                        var method = 'put';

                        HTTP.request_actions(obj, method)
                        .success(function(data) {
                            
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'Параметры топлива изменены');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });

                        break;

            case 'azs' : 
                        var obj = {};
                        obj.name = data.name;
                        obj.adress = data.adress;

                        obj.action = 'azs';
                        var method = 'post';

                        HTTP.request_actions(obj, method)
                        .success(function(data) {
                           
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'АЗС добавлена');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });
                        break;

            case 'azsEdit' : 
                        var obj = {};
                        obj.action = 'azs';
                        obj.name = data.name;
                        obj.id = data.id;
                        obj.adress = data.adress;

                        var method = 'put';

                        HTTP.request_actions(obj, method)
                        .success(function(data) {
                            
                            //Обновляем добавленные данные 
                            getAllProperties();

                            growl.success( 'Параметры АЗС изменены');
                            
                        }).error(function(msg, code) {
                            console.log('error');
                        });

                        break;
        }
        hideOptionsForm();

    }


//для isteven-multi-select, пока не используется
// $scope.inputCars = {};    
// $scope.outputCars = {};  


//фильтр по таблице с заправками
    $scope.refillsFilter = refillsFilter;
    function refillsFilter()
    {
        // if( $scope.filter.cars[0] || $scope.filter.fuels[0] || $scope.filter.azss[0] )
        // { 
            var obj = {};
         
            // если в html d SELECT 
            //     ng-options="value.name for value in allCars track by value.id"
            // используется внутри <select ...> </select> 
            if( $scope.filter.cars.length>0 )
            {
                var carsIDs = [];
                $scope.refillsFilterCar = $scope.filter.cars[0].id;
                for (var i = 0; i < $scope.filter.cars.length; i++) {
                    carsIDs.push($scope.filter.cars[i].id);
                }
            }

            if( $scope.filter.fuels.length>0 )
            {
                var fuelsIDs = [];
                for (var i = 0; i < $scope.filter.fuels.length; i++) {
                    fuelsIDs.push($scope.filter.fuels[i].id);
                }
            }

            if( $scope.filter.azss.length>0 )
            {
                var azssIDs = [];
                for (var i = 0; i < $scope.filter.azss.length; i++) {
                    azssIDs.push($scope.filter.azss[i].id);
                }
            }

            if( carsIDs || fuelsIDs || azssIDs )
            {
                obj.cars = carsIDs;
                obj.fuels = fuelsIDs;
                obj.azss = azssIDs;
                // console.log( obj );
            }
            // */

            // if( $scope.filter.cars.length>0 )
            // {
            //     obj.cars = $scope.filter.cars;
            //     // if
            // }


            $scope.dataIsReceiving = true;
            preLoadDataTableJobs( true, obj );

            growl.success( 'Фильтр применен' );
        // }
    }


//очистка всех фильтров по таблице
    $scope.refillsFilterClear = refillsFilterClear;
    function refillsFilterClear()
    {
        $scope.filter.cars = [];
        $scope.filter.fuels = [];
        $scope.filter.azss = [];
    }


//очистка  фильтра азс
    $scope.refillsFilterAzssClear = refillsFilterAzssClear;
    function refillsFilterAzssClear()
    {
        $scope.filter.azss = [];    
    }


//очистка  фильтра машин
    $scope.refillsFilterCarsClear = refillsFilterCarsClear;
    function refillsFilterCarsClear()
    {
        $scope.filter.cars = [];    
    }

//очистка  фильтра топлива
    $scope.refillsFilterFuelsClear = refillsFilterFuelsClear;
    function refillsFilterFuelsClear()
    {
        $scope.filter.fuels = [];    
    }

//тест
    $scope.filterCars = filterCars;
    function filterCars(){
        
        // $scope.filter.cars = [1,2,3];
        // console.log( $scope.filter.cars);
        // if

    }

//-----------------------    

}])



function removeRefill(elemId, id) {

    console.log(elemId, id);
  var scope = angular.element(document.getElementById(elemId)).scope();
  scope.$apply(function(){
    scope.removeRefill(id);
  });
};

