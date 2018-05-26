function autoSuggest() {
    emptyDivs();
    var data = $("#inputForm").serialize();
    $('#progressBar').show();
    $.ajax({
        url: "ajax_restcountries.php?action=autoSuggest",
        data: data,
        cache: $('#enableCache').prop('checked'),
        type: 'POST',
        dataType: 'json'
    }).done(function (json) {
        emptyDivs();
        if (json.status === 'success') {
            if (json.count > 1) {
                var countries = json.data;
                $.each(countries, function (index, item) {
                    $('#possibleCountries').append('<li id="select-' + index + '"><div class="cntrCode">' + item.alpha3Code + '</div><div class="cntrFlag"><img src="' + item.flag + '"/></div><div class="cntrName">' + item.name + '</div></li>');
                });
                var summaryHTML = json.summeryHTML;
                $('#searchSummary').html(summaryHTML);
                $('#possibleCountries').slideDown();
            }
            if (json.count == '1') {
                $('#countryDetails').html(json.details);
            }
        }
        else {
            errors = json.errors;
            $.each(errors, function (index, item) {
                if (item.severity == '1') {
                    $('#errorAlertBox').append('<div class="alert alert-danger"><strong>Warning!</strong>' + item.message + '</div>');
                }
                else {
                    $('#errorAlertBox').append('<div class="alert alert-warning"><strong>Warning!</strong>' + item.message + '</div>');
                }
            });
        }
        $('#progressBar').hide();
    });
}

function searchCountries() {
    emptyDivs();
    $('#progressBar').show();
    var data = $("#inputForm").serialize();
    $.ajax({
        url: "ajax_restcountries.php?action=search",
        data: data,
        cache: $('#enableCache').prop('checked'),
        type: 'POST',
        dataType: 'json'
    }).done(function (json) {
        emptyDivs();
        if (json.status === 'success') {
            if (json.count > 1) {
                var countries = json.data;
                var tableRow = '';
                $.each(countries, function (index, item) {
                    $('#countryResultsTable div table tbody').append(
                        '<tr><td>' + item.alpha3Code + '</td><td>' + item.name + '</td><td><img src="' + item.flag + '" title="' + item.name + '"/></td><td>' + item.region + '</td><td>' + item.subregion + '</td><td><a onclick="showCountry(\''+item.alpha3Code+'\')" </td></tr>'
                    );
                    $('#possibleCountries').append('<li id="select-' + index + '" onClick="showCountry(\''+index+'\')"><div class="cntrCode">' + item.alpha3Code + '</div><div class="cntrFlag"><img src="' + item.flag + '"/></div><div class="cntrName">' + item.name + '</div></li>');
                });
                var summaryHTML = json.summeryHTML;
                $('#searchSummary').html(summaryHTML);
                $('#countryResultsTable').slideDown();

            }
            if (json.count == '1') {
                $('#countryDetails').html(json.details);
            }
        }
        else {
            errors = json.errors;
            $.each(errors, function (index, item) {
                if (item.severity == '1') {
                    $('#errorAlertBox').append('<div class="alert alert-danger"><strong>Warning!</strong>' + item.message + '</div>');
                }
                else {
                    $('#errorAlertBox').append('<div class="alert alert-warning"><strong>Warning!</strong>' + item.message + '</div>');
                }
            });
        }
        $('#progressBar').hide();
    });
}
function showCountry(code){
    $('#country').val(code);
    searchCountries();
}

function emptyDivs() {
    $('#possibleCountries').hide();
    $('#possibleCountries').empty();
    $('#errorAlertBox').empty();
    $('#searchSummary').empty();
    $('#countryResultsTable').hide();
    $('#countryResultsTable div table tbody').empty();
    $('#countryDetails').empty();
}


$(document).ready(function () {
    $("#country").keyup(function () {
        var country = $('#country').val();
        if ($('#searchSuggest').prop('checked') == true) {
            autoSuggest();
        }
    });
    $('#inputForm').submit(function (e) {
        searchCountries();
        e.preventDefault();
    });

});