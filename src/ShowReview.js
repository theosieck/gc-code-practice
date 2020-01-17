import Options from './Options';

const ShowReview = (props) => (
    <div>
        <p>Grader 1 level: {props.levelTitles[props.reviewSet[props.subNum]['judg_level_1']]}</p>
        <p>Grader 2 level: {props.levelTitles[props.reviewSet[props.subNum]['judg_level_2']]}</p>
        <p>Grader 1 rationale: {props.reviewSet[props.subNum]['rationale_1']}</p>
        <p>Grader 2 rationale: {props.reviewSet[props.subNum]['rationale_2']}</p>
        {!props.conVisible && 
            <Options 
                handleChoice={props.handleChoice}
                levelTitles={props.levelTitles}
            />
        }
    </div>
);

export default ShowReview;