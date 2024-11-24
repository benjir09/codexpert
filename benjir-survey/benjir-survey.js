
var $ = jQuery.noConflict();



function getqans(total_ans) {
  
  if (total_ans.value == 2) {
    $("#question-answers").html('');
    $("#question-answers").append('<input type="text" id="ans-one" name="ans_one" value="" placeholder="Answer one"><br>');
    $("#question-answers").append('<input type="text" id="ans-two" name="ans_two" value="" placeholder="Answer two"><br>');
    $("#correct-ans option[value='3']").remove();
    $("#correct-ans option[value='4']").remove();
  }
  if (total_ans.value == 3) {
    $("#question-answers").html('');
    $("#question-answers").append('<input type="text" id="ans-one" name="ans_one" value="" placeholder="Answer one"><br>');
    $("#question-answers").append('<input type="text" id="ans-two" name="ans_two" value="" placeholder="Answer two"><br>');
    $("#question-answers").append('<input type="text" id="ans-three" name="ans_three" value="" placeholder="Answer three"><br>');
    $("#correct-ans").append('<option value="3">3</option>');
    $("#correct-ans option[value='4']").remove();
  }
  if (total_ans.value == 4) {
    $("#question-answers").html('');
    $("#question-answers").append('<input type="text" id="ans-one" name="ans_one" value="" placeholder="Answer one"><br>');
    $("#question-answers").append('<input type="text" id="ans-two" name="ans_two" value="" placeholder="Answer two"><br>');
    $("#question-answers").append('<input type="text" id="ans-three" name="ans_three" value="" placeholder="Answer three"><br>');
    $("#question-answers").append('<input type="text" id="ans-four" name="ans_four" value="" placeholder="Answer four"><br>');
    $("#correct-ans").append('<option value="3">3</option>');
    $("#correct-ans").append('<option value="4">4</option>');
  }
}

function save_question() {
  let question_name = $("input[name=question_name]").val();
  let question_type = $("#question-type").select2("data")[0].id;
  let question_total_answer = $("#question-total-answer").select2("data")[0].id;
  let ans_one = $("input[name=ans_one]").val();
  let ans_two = $("input[name=ans_two]").val();
  let ans_three = $("input[name=ans_three]").val();
  let ans_four = $("input[name=ans_four]").val();
  let correct_ans = $("#correct-ans").select2("data")[0].id;
  let save_nonce = $("input[name=name_of_your_nonce_field]").val();

  if (!question_name || !ans_one || !ans_two) {
    alert('Please fill form fields');
    return false;
  }

  jQuery.ajax({
    type: "POST",
    dataType: "json",
    url: ajax_object.ajax_url,
    data: {
      action: "benjir_survey_save_question",
      question_name: question_name,
      question_type: question_type,
      ans_one: ans_one,
      ans_two: ans_two,
      ans_three: ans_three,
      ans_four: ans_four,
      correct_ans: correct_ans,
      save_nonce:save_nonce
    },
    // beforeSend: function () {
    //   selected_program.show();
    // },
    success: function (response) {
      if (!response || response.error) return;
      if (response.status == "ok") {
        location.reload();
      } else {
        $("#responsemessage").html("Data Not Verified");
        alert("Response else");
      }
    },
  });
}

function delete_question(delete_question_id) {
  let to_delete = delete_question_id;
  let delete_nonce = $("input[name=name_of_your_nonce_field]").val();


  jQuery.ajax({
    type: "POST",
    dataType: "json",
    url: ajax_object.ajax_url,
    data: {
      action: "benjir_survey_delete_question",
      to_delete: to_delete,
      delete_nonce:delete_nonce
    },
    success: function (response) {
      if (!response || response.error) return;
      if (response.status == "ok") {
        location.reload();
      } else {
        alert("Response else");
      }
    },
  });

}


function save_survey() {
  let survey_name = $("input[name=survey_name]").val();
  let allquestions = [];
  $('input[name="checkquestion"]:checked').each(function() {
    allquestions.push(this.value);
  });

  allquestions = allquestions.join(",");
  
  let save_nonce = $("input[name=name_of_your_nonce_field]").val();

  if (!survey_name) {
    console.log(allquestions);
    alert('Please fill form fields');
    return false;
  }

  jQuery.ajax({
    type: "POST",
    dataType: "json",
    url: ajax_object.ajax_url,
    data: {
      action: "benjir_survey_save_survey",
      survey_name: survey_name,
      allquestions: allquestions,
      save_nonce: save_nonce
    },
    // beforeSend: function () {
    //   selected_program.show();
    // },
    success: function (response) {
      if (!response || response.error) return;
      if (response.status == "ok") {
        location.reload();
      } else {
        $("#responsemessage").html("Data Not Verified");
        alert("Response else");
      }
    },
  });
}


function delete_survey(delete_survey_id) {
  let to_delete = delete_survey_id;
  let delete_nonce = $("input[name=name_of_your_nonce_field]").val();


  jQuery.ajax({
    type: "POST",
    dataType: "json",
    url: ajax_object.ajax_url,
    data: {
      action: "benjir_survey_delete_survey",
      to_delete: to_delete,
      delete_nonce:delete_nonce
    },
    success: function (response) {
      if (!response || response.error) return;
      if (response.status == "ok") {
        location.reload();
      } else {
        alert("Response else");
      }
    },
  });

}



$(document).ready(function () {
  $(window).load(function () {

    let table = new DataTable('#questions-table');
    let surveytable = new DataTable('#survey-table');
    let survey_selectbox = new DataTable('#survey-selectbox');
    

    $(".selec2plug").select2({
      placeholder: "Select an option",
    });

  });
});
