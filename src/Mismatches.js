const Mismatches = (props) => (
    <div>
        <p>{props.disagreements} disagreements out of {props.total} total cases.</p>
        {(props.disagreements < props.total) && <div>
            <button style={{marginRight:'1em'}} onClick={() => props.saveMatches(true)}>Save matched cases and continue</button>
            <button onClick={() => props.saveMatches(false)}>Continue without saving matched cases</button>
        </div>}
        {(props.disagreements >= props.total) && <div>
            <p>No matches to save.</p>
            <button onClick={() => props.saveMatches(false)}>Continue</button>
        </div>}
    </div>
)

export default Mismatches;