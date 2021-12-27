@extends('admin.layouts.master')
@section('content')
<div _ngcontent-dsw-10="" class="content">
    <router-outlet _ngcontent-jmq-10=""></router-outlet>
    <app-index _nghost-jmq-23=""><div _ngcontent-jmq-23="" class="page-header">
            <h1 _ngcontent-jmq-23="">
                Categories
            </h1>
            <div _ngcontent-jmq-23="" class="page-actions ml-auto">
                <a _ngcontent-jmq-23="" style="margin-right:5px;" href="//api.setanta.ge/api/categories/export">
                    <button _ngcontent-jmq-23="" class="btn btn-primary">Export</button>
                </a>
                <button _ngcontent-jmq-23="" class="btn btn-primary btn--raised" routerlink="/categories/add">Add Category</button>
                <button _ngcontent-jmq-23="" class="btn btn-success btn--raised" routerlink="/categories/reorder">Reorder</button>
            </div>
        </div>
        <div _ngcontent-jmq-23="" class="categories">
            <div _ngcontent-jmq-23="" class="categories__header">
                <div _ngcontent-jmq-23="" class="categories__title">
                    Title
                </div>
                <div _ngcontent-jmq-23="" class="categories__articles">
                    Articles Count
                </div>
                <div _ngcontent-jmq-23="" class="categories__users">
                    Users Count
                </div>
                <div _ngcontent-jmq-23="" class="categories__actions">
                    Actions
                </div>
            </div>
            <!--template bindings={}--><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}-->
                                <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/UlRXAf9uDN2qQMMzR2ikIzQpdrsg7kkF.png">
                            </div>
                            <div class="category__title">
                                ფეხბურთი
                            </div>
                        </div>
                        <div class="category__articles">
                            13473
                        </div>
                        <div class="category__users">
                            38
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}--><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ჩ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ჩემპიონატები
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        8988
                                    </div>
                                    <div class="category__users">
                                        2
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}--><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}--><div>
                                                            F
                                                        </div>
                                                        <!--template bindings={}-->
                                                    </div>
                                                    <div class="category__title">
                                                        FA CUP
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    97
                                                </div>
                                                <div class="category__users">
                                                    1
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/RQIjPIONZjz1Qh7VrZcmuwejoxDfIU7x.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ეროვნული ლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    790
                                                </div>
                                                <div class="category__users">
                                                    7
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/t7MyJ8TcVCTzFeDfOmeRFcxwqJTWoglh.png">
                                                    </div>
                                                    <div class="category__title">
                                                        სერია ა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    1609
                                                </div>
                                                <div class="category__users">
                                                    46
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/ptALMmoAGSEUYzbM1meCJUeYuoIKqPMo.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ბუნდესლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    773
                                                </div>
                                                <div class="category__users">
                                                    12
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/TfHcbjyyQ8c694FECSk7xED5LhQoz3Eb.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ლიგა 1
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    679
                                                </div>
                                                <div class="category__users">
                                                    5
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/GteO1mR4x6MGDlTlWMOElN9bI4ptJ2it.png">
                                                    </div>
                                                    <div class="category__title">
                                                        პრემიერ ლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    2876
                                                </div>
                                                <div class="category__users">
                                                    16
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/gpLSxAbO2BOnSleLPLqTJFrDlvgTwj3i.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ლა ლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    2576
                                                </div>
                                                <div class="category__users">
                                                    17
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/risJXz670HDT4NtOWJfvoSDSvaqGChIp.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ერედივიზიონი
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    48
                                                </div>
                                                <div class="category__users">
                                                    2
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category>
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ე
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ევროტურნირები
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        1498
                                    </div>
                                    <div class="category__users">
                                        2
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}--><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/MmlaQYIm5DWvr2ZfOJMHXvj4m0at68d9.png">
                                                    </div>
                                                    <div class="category__title">
                                                        ჩემპიონთა ლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    1641
                                                </div>
                                                <div class="category__users">
                                                    64
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}--><div>
                                                            ე
                                                        </div>
                                                        <!--template bindings={}-->
                                                    </div>
                                                    <div class="category__title">
                                                        ევროპის ლიგა
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    312
                                                </div>
                                                <div class="category__users">
                                                    5
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category>
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ს
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            სანაკრებო ჩემპიონატები
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        1431
                                    </div>
                                    <div class="category__users">
                                        2
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}--><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}-->
                                                        <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/4upmuwwouyXkh0vo0dznHF4l2dWSVAqt.png">
                                                    </div>
                                                    <div class="category__title">
                                                        მსოფლიო ჩემპიონატი
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    1192
                                                </div>
                                                <div class="category__users">
                                                    15
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category><app-category><div class="category">
                                            <div class="category__main">
                                                <div style="width:60%; display:flex; align-items: center;">
                                                    <div class="category__image">
                                                        <!--template bindings={}--><div>
                                                            ე
                                                        </div>
                                                        <!--template bindings={}-->
                                                    </div>
                                                    <div class="category__title">
                                                        ევროპის ჩემპიონატი
                                                    </div>
                                                </div>
                                                <div class="category__articles">
                                                    11
                                                </div>
                                                <div class="category__users">
                                                    7
                                                </div>
                                                <div class="category__actions">
                                                    <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                                        <i class="material-icons">more_vert</i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <button class="dropdown-item" type="button">
                                                            Edit
                                                        </button>
                                                        <button class="dropdown-item" type="button">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="category__children">
                                                <!--template bindings={}-->
                                            </div>
                                        </div></app-category>
                                </div>
                            </div></app-category>
                    </div>
                </div></app-category><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}-->
                                <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/3zMSo7zGBcEz5gAbcnb12VEyf7GFZH5I.png">
                            </div>
                            <div class="category__title">
                                კალათბურთი
                            </div>
                        </div>
                        <div class="category__articles">
                            2343
                        </div>
                        <div class="category__users">
                            12
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}--><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                N
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            NBA
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        1720
                                    </div>
                                    <div class="category__users">
                                        19
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ს
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            სანაკრებო
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        189
                                    </div>
                                    <div class="category__users">
                                        3
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ე
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ევროლიგა
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        133
                                    </div>
                                    <div class="category__users">
                                        0
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category>
                    </div>
                </div></app-category><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}-->
                                <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/eohEccM22vUi5mlOaQzYAd0iuoL1W78X.png">
                            </div>
                            <div class="category__title">
                                რაგბი
                            </div>
                        </div>
                        <div class="category__articles">
                            1511
                        </div>
                        <div class="category__users">
                            17
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}--><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}-->
                                            <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/AfUqXGAk3XT2gGjwoD66YerXUeSNIlMw.png">
                                        </div>
                                        <div class="category__title">
                                            ტოპ 14
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        142
                                    </div>
                                    <div class="category__users">
                                        23
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                დ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            დიდი 10
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        104
                                    </div>
                                    <div class="category__users">
                                        0
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ს
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            საკლუბო
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        576
                                    </div>
                                    <div class="category__users">
                                        0
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ს
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            სანაკრებო
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        720
                                    </div>
                                    <div class="category__users">
                                        4
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category>
                    </div>
                </div></app-category><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}-->
                                <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/uaVC7b5nUZa0LRDUdWMIAC60lTI1C6E3.png">
                            </div>
                            <div class="category__title">
                                სხვა
                            </div>
                        </div>
                        <div class="category__articles">
                            871
                        </div>
                        <div class="category__users">
                            7
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}--><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                F
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            F1
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        139
                                    </div>
                                    <div class="category__users">
                                        7
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}-->
                                            <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/fHG8TVb618Yvxk4FGyZMbCNcPcHTEGZ1.png">
                                        </div>
                                        <div class="category__title">
                                            ძიუდო
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        335
                                    </div>
                                    <div class="category__users">
                                        2
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                U
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            UFC
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        132
                                    </div>
                                    <div class="category__users">
                                        9
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}-->
                                            <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/JSTfSDlr0Wm1ifmKy89pw7WypSDzpNO4.png">
                                        </div>
                                        <div class="category__title">
                                            ჩოგბურთი
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        266
                                    </div>
                                    <div class="category__users">
                                        3
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category>
                    </div>
                </div></app-category><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}-->
                                <!--template bindings={}--><img src="//storage.setanta.ge/images/categories/5cQ5Nae5ipO2r2iPV6XOH4k7Hz7TfwgS.png">
                            </div>
                            <div class="category__title">
                                ქართული
                            </div>
                        </div>
                        <div class="category__articles">
                            2843
                        </div>
                        <div class="category__users">
                            30
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}--><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ლ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ლეგიონერები
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        510
                                    </div>
                                    <div class="category__users">
                                        3
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ფ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ფეხბურთი
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        1185
                                    </div>
                                    <div class="category__users">
                                        9
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                კ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            კალათბურთი
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        273
                                    </div>
                                    <div class="category__users">
                                        2
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                რ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            რაგბი
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        341
                                    </div>
                                    <div class="category__users">
                                        4
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ჩ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ჩოგბურთი
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        55
                                    </div>
                                    <div class="category__users">
                                        0
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category><app-category><div class="category">
                                <div class="category__main">
                                    <div style="width:60%; display:flex; align-items: center;">
                                        <div class="category__image">
                                            <!--template bindings={}--><div>
                                                ძ
                                            </div>
                                            <!--template bindings={}-->
                                        </div>
                                        <div class="category__title">
                                            ძიუდო
                                        </div>
                                    </div>
                                    <div class="category__articles">
                                        149
                                    </div>
                                    <div class="category__users">
                                        1
                                    </div>
                                    <div class="category__actions">
                                        <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                            <i class="material-icons">more_vert</i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" type="button">
                                                Edit
                                            </button>
                                            <button class="dropdown-item" type="button">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category__children">
                                    <!--template bindings={}-->
                                </div>
                            </div></app-category>
                    </div>
                </div></app-category><app-category _ngcontent-jmq-23=""><div class="category">
                    <div class="category__main">
                        <div style="width:60%; display:flex; align-items: center;">
                            <div class="category__image">
                                <!--template bindings={}--><div>
                                    C
                                </div>
                                <!--template bindings={}-->
                            </div>
                            <div class="category__title">
                                CyberSport
                            </div>
                        </div>
                        <div class="category__articles">
                            4
                        </div>
                        <div class="category__users">
                            0
                        </div>
                        <div class="category__actions">
                            <button class="btn btn--transparent btn--flex" data-toggle="dropdown" type="button">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item" type="button">
                                    Edit
                                </button>
                                <button class="dropdown-item" type="button">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="category__children">
                        <!--template bindings={}-->
                    </div>
                </div></app-category>
        </div></app-index>
    </div>
@endsection