// CI4: one file for all environments. URLs are built from config.baseURL (set in core_i18n.js),
// replacing dmdtrack-development.js / dmdtrack-production.js which hardcoded the server path.
/**
 * Default theme functions
 */
$(document).ready(function() {

  function formatConcat(item) { return item.text + " : " + item.text2;}

  $('#referrerSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      url: config.baseURL + 'referrer/GetReferrerSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        //console.table(data);
        return {
          results: data
        };
        // return {
        //   // combine Institution+referrerLastName
        //   results: $.map(data, function(obj){
        //     return {id: obj.id, text: obj.text+" "+obj.text2};
        //   })
        // };
      },
      cache: true
      //templateResult: formatConcat,
      //templateSelection: formatConcat
    }
  });
  // testing
  $('#showValue').click(function () {
      $('#output').text($('#consentTypeSearch').val());
  });

  $('#sampleSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      url: config.baseURL + 'sample/GetSampleSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        //console.table(data);
        return {
          results: data
        };
      },
      cache: true      
    }
  });

  $('#sampleParentSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      //url: config.baseURL + 'sample/GetSampleParentSearch',
      url: config.baseURL + 'sample/GetSampleSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        console.table(data);
        return {
          results: data
        };
      },
      cache: true      
    }      
  });
  
  $('#contactSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      url: config.baseURL + 'contact/GetContactSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        //console.table(data);consentTypeSearch
        return {
          results: data
        };
      },
      cache: true      
    }
  });

  $('#individualSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      url: config.baseURL + 'individual/GetIndividualSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        //console.table(data);
        return {
          results: data
        };
      },
      cache: true      
    }
  });

  $('#consentTypeSearch').select2({
    placeholder: 'Select an option',
    ajax: {
      url: config.baseURL + 'consent/GetConsentSearch',
      dataType: 'json',
      delay: 250,
      method: 'GET',
      processResults: function(data) {
        //console.table(data);
        return {
          results: data
        };
      },
      cache: true      
    }
  })
    
  

  // --------- PRE POPULATE DROPDOWNS --------------
  // Fetch the preselected item, and add to the control

  // Consent/add: Individual select2
  var individualSelect = $('#individualSearch');
  var individualID = $('#dataIndividualID').val();
  var individualName = $('#dataIndividualName').val();
  var option = new Option(individualName, individualID, true, true);
  individualSelect.append(option).trigger('change');

  var consentTypeSelect = $('#consentTypeSearch');
  var consentTypeID = $('#dataConsentTypeID').val();
  var consentType = $('#dataConsentType').val();
  var option = new Option(consentType, consentTypeID, true, true);
  consentTypeSelect.append(option).trigger('change');

  var contactSelect = $('#contactSearch');
  var contactID = $('#dataContactID').val();
  var contactName = $('#dataContact').val();
  var option = new Option(contactName, contactID, true, true);
  contactSelect.append(option).trigger('change');

  var referrerSelect = $('#referrerSearch');
  var referrerID = $('#dataReferrerID').val();
  var referrerName = $('#dataReferrer').val();
  var option = new Option(referrerName, referrerID, true, true);
  referrerSelect.append(option).trigger('change');

  // Used on Result and Sample edit
  var sampleSelect = $('#sampleSearch');
  var sampleID = $('#dataSampleID').val();
  var sampleName = $('#dataSample').val();
  console.log(sampleID);
  var option = new Option(sampleName, sampleID, true, true);
  sampleSelect.append(option).trigger('change');
  
  var sampleParentSelect = $('#sampleParentSearch');
  var sampleParentID = $('#dataSampleParentID').val();
  var sampleParentName = $('#dataSampleParent').val();
  if (sampleParentID!=""){
     var option = new Option(sampleParentName, sampleParentID, true, true);
     sampleParentSelect.append(option).trigger('change');
  }
  

  // $.ajax({
  //     method: 'GET',
  //     url: config.baseURL + 'indiv/GetOneIndividualSearch/' + indivData.id , //this value is generated on consent_form/edit
  //     dataType: 'json'
  // }).then(function (data) {
  //     // create the option and append to Select2
  //     console.log( $('dataIndividualID').val());
  //     //console.log(data[0].id);
  //     var option = new Option(data[0].text, data[0].id, true, true);
  //     individualSelect.append(option).trigger('change');
  //
  //     // manually trigger the `select2:select` event
  //     individualSelect.trigger({
  //         type: 'select2:select',
  //         params: {
  //             data: data
  //         }
  //     });
  // });
  //var newOption = new Option(indivData.text, indivData.id, true, true);
  //$('#individualSearch').append(newOption).trigger('change');

});
