Survey
    .StylesManager
    .applyTheme("default");

var json = {
    "questions": [
        {
            "type": "radiogroup",
            "name": "one",
            "title": "Radiogroup question (green)",
            "choices": ["Yes", "No"]
        }, {
            "type": "checkbox",
            "name": "two",
            "title": "Checkbox question (orange)",
            "choices": ["One", "Two"]
        }, {
            "type": "radiogroup",
            "name": "three",
            "title": "Required question (red title)",
            "isRequired": true,
            "choices": ["Yes", "No"]
        }

    ]
};

window.survey = new Survey.Model(json);

survey
    .onComplete
    .add(function (result) {
        document
            .querySelector('#surveyResult')
            .textContent = "Result JSON:\n" + JSON.stringify(result.data, null, 3);
    });

survey
    .onUpdateQuestionCssClasses
    .add(function (survey, options) {
        var classes = options.cssClasses

        classes.root = "sq-root";
        classes.title = "sq-title";
        classes.item = "sq-item";
        classes.label = "sq-label";

        if (options.question.isRequired) {
            classes.title = "sq-title sq-title-required";
            classes.root = "sq-root sq-root-required";
        }

        if (options.question.getType() === "checkbox") {
            classes.root = "sq-root sq-root-cb";
        }
    });

$("#surveyElement").Survey({model: survey});
