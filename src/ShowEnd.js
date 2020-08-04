
const ShowEnd = (props) => (
    <div>
        <h4>You're all done!</h4>
				<p>Score: {(props.numCorrect / props.total * 100).toFixed(0)}%</p>
        <p>Close this tab to return to the Practice Home.</p>
    </div>
);

export default ShowEnd;
