@extends('admin.layouts.master')
@section('content')
<div _ngcontent-dsw-10="" class="content">
    <router-outlet _ngcontent-dsw-10=""></router-outlet>
    <app-dashboard _nghost-dsw-7="">
        <div _ngcontent-dsw-7="" class="page-header">
            <h1 _ngcontent-dsw-7="">
                Dashboard
            </h1>
        </div>
        <div _ngcontent-dsw-7="" class="row">
            <div _ngcontent-dsw-7="" class="col-6">
                <div _ngcontent-dsw-7="" class="card">
                    <div _ngcontent-dsw-7="" class="card-block">
                        <h4 _ngcontent-dsw-7="" class="card-title m-0">Users</h4>
                    </div>
                    <ul _ngcontent-dsw-7="" class="list-group list-group-flush">
                        <li _ngcontent-dsw-7="" class="list-group-item d-flex">
                            Today
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                        <li _ngcontent-dsw-7="" class="list-group-item">
                            Average per day
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                        <li _ngcontent-dsw-7="" class="list-group-item">
                            Total
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                    </ul>
                </div>
            </div>
            <div _ngcontent-dsw-7="" class="col-6">
                <div _ngcontent-dsw-7="" class="card">
                    <div _ngcontent-dsw-7="" class="card-block">
                        <h4 _ngcontent-dsw-7="" class="card-title m-0">Comments</h4>
                    </div>
                    <ul _ngcontent-dsw-7="" class="list-group list-group-flush">
                        <li _ngcontent-dsw-7="" class="list-group-item d-flex">
                            Today
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                        <li _ngcontent-dsw-7="" class="list-group-item">
                            Average per day
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                        <li _ngcontent-dsw-7="" class="list-group-item">
                            Total
                            <div _ngcontent-dsw-7="" class="badge badge-default ml-auto"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div _ngcontent-dsw-7="" class="row mt-4">
            <div _ngcontent-dsw-7="" class="col-12">
                <div _ngcontent-dsw-7="" class="card">
                    <div _ngcontent-dsw-7="" class="card-block">
                        <div _ngcontent-dsw-7="" class="float-right">
                            <form _ngcontent-dsw-7="" class="d-flex ng-untouched ng-pristine ng-valid">
                                <div _ngcontent-dsw-7="" class="form-group mb-0 mr-3">
                                    <input _ngcontent-dsw-7="" class="form-control ng-untouched ng-pristine ng-valid hasDatepicker" name="article_stats_date_from" type="text" id="dp1533843956636">
                                </div>
                                <div _ngcontent-dsw-7="" class="form-group mb-0 mr-3">
                                    <input _ngcontent-dsw-7="" class="form-control ng-untouched ng-pristine ng-valid hasDatepicker" name="article_stats_date_till" type="text" id="dp1533843956637">
                                </div>
                                <div _ngcontent-dsw-7="" class="form-group mb-0">
                                    <input _ngcontent-dsw-7="" class="btn btn-primary" type="submit" value="Filter">
                                </div>
                            </form>
                        </div>
                        <h4 _ngcontent-dsw-7="" class="card-title m-0">Article Stats</h4>
                    </div>
                    <table _ngcontent-dsw-7="" class="table mb-0">
                        <thead _ngcontent-dsw-7="">
                        <tr _ngcontent-dsw-7="">
                            <th _ngcontent-dsw-7="">
                                User
                            </th>
                            <th _ngcontent-dsw-7="">
                                Articles
                            </th>
                            <th _ngcontent-dsw-7="">
                                Views
                            </th>
                        </tr>
                        </thead>
                        <tbody _ngcontent-dsw-7="">
                        <!--template bindings={}-->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </app-dashboard>
</div>
@endsection