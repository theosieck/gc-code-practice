import Row from './Row';

const genMatches = (codes,matches,setMatches) => {
    const matchArray = [];
    for(let i=1;i<=codes.length;i++) {
        if(matches[i] && matches[i][0] != '' && matches[i][1] != '') {
            matchArray[i] = [];
            matchArray[i][0] = [codes[i],matches[i][0]];
            matchArray[i][1] = [codes[i],matches[i][1]];
        }
    }
    setMatches(matchArray);
    return matchArray;
}

const Matches = (props) => {
    let matches = genMatches(props.codes,props.matches,props.setMatches);
    return (
        <div style={{marginTop: '50px'}}>
        <h2>Matches:</h2>
        {(matches.length>0) && matches.map((match) => (
            <div>
            <Row
                code={match[0][0]}
                selection={match[0][1]}
            />
            <Row
                code={match[1][0]}
                selection={match[1][1]}
            />
            </div>
        ))}
        {(matches.length<=0) && <p>No matches to display.</p>}
        </div>
    )
}

export default Matches;