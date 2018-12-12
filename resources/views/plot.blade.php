@extends('layouts.layout')

@section('content')
    <script src="{{asset('js/chart.min.js')}}"></script>
    <canvas id="line-chart" width="5" height="5"></canvas>
    <style>
        #line-chart {
            width:65% !important;
            height:50% !important;
        }
    </style>
    <script type="text/javascript">
        function random_rgba() {
            var o = Math.round, r = Math.random, s = 255;
            return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + r().toFixed(1) + ')';
        }
        function getRandomRolor() {
            var letters = '012345'.split('');
            var color = '#';        
            color += letters[Math.round(Math.random() * 5)];
            letters = '0123456789ABCDEF'.split('');
            for (var i = 0; i < 5; i++) {
                color += letters[Math.round(Math.random() * 15)];
            }
            return color;
        }
        var labels = {!! json_encode($labels) !!};
        var data = {!! json_encode($data) !!};
        for(var i in data) {
            data[i]['fill'] = false;
            data[i]['borderColor'] = getRandomRolor();
        }
        new Chart(document.getElementById("line-chart"), {
            type: 'line',
            data: {
                labels: Object.values(labels),
                datasets: data
            },
            options: {
                title: {
                    display: true,
                    text: 'Wykres'
                }
            }
        });
    </script>
@endsection