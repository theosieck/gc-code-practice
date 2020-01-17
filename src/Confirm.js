
const Confirm = (props) => (
    <div>
        <p>You chose: <b>{props.choice}</b></p>
        {props.showRat && 
            <p>Rationale:
            <br />
            {props.rationale}</p>
        }
        <button onClick={props.handleRevise}>Revise</button><button onClick={props.handleNext}>Next</button>
    </div>
);

export default Confirm;