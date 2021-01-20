import Row from './Row';


const ShowFeedback = (props) => {
	return (
    <div>
			<h2>Correct Codes:</h2>
			{/*{props.correctCodes.length>0 && props.correctCodes.map((code) => {
				const codeLabel = props.codeLabels[code];
				if(codeLabel) {
					return (<p>{code}. {codeLabel}</p>)
				}
			})}*/}
			{props.correctRows.length>0 && props.correctRows.map((row) => (
	        <Row
	            selection={row.text}
	            code={row.code}
	            showDelete={false}
	        />
	    ))}
			{props.correctRows.length<=0 && <p>No codes to show.</p>}

			<h2>Missed Codes:</h2>
			{/*props.missedCodes.length>0 && props.missedCodes.map((code) => {
				const codeLabel = props.codeLabels[code];
				if(codeLabel) {
					return (<p>{code}. {codeLabel}</p>)
				}
			})*/}
			{props.missedRows.length>0 && props.missedRows.map((row) => {
				return (
	        <Row
	            selection={row.text}
	            code={row.code}
	            showDelete={false}
	        />
				)
	    })}
			{props.missedRows.length<=0 && <p>No codes to show.</p>}

			<h2>False Positives:</h2>
			{props.falsePositives.length>0 && props.falsePositives.map((code) => {
				const codeLabel = props.codeLabels[code];
				if(codeLabel) {
					return (<p>{code}. {codeLabel}</p>)
				}
			})}
			{props.falsePositives.length<=0 && <p>No codes to show.</p>}

			<button style={{marginTop:"10px"}} onClick={props.handleNext}>Next</button>
    </div>
	);
}

export default ShowFeedback;
