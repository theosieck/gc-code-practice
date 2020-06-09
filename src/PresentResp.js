import ReactHtmlParser from 'react-html-parser';
import SelectionHighlighter from 'react-highlight-selection';

// const divStyle = {
//     marginTop: '50px'
// };

const PresentResp = (props) => (
    <div>
        <h2>Case: {props.respId}</h2>
        <SelectionHighlighter
            text={ReactHtmlParser(props.response)}
            selectionHandler={props.handleSelection}
        />
    </div>
);

export default PresentResp;