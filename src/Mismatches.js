const Mismatches = (props) => (
    <div>
        <p>Click below to review {props.disagreements} disagreements out of {props.total} total cases.</p>
        <button onClick={() => props.saveMatches(false)}>Continue</button>
    </div>
)

export default Mismatches;