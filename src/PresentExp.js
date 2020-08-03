import ReactHtmlParser from 'react-html-parser';
import Highlightable from 'highlightable';

const PresentExp = (props) => (
    <div>
        <h2>Case: {props.expId}</h2>
        <div onMouseUp={() => {props.handleSelection(window.getSelection().toString())}}>{ReactHtmlParser(props.response)}</div>
        {/*<Highlightable
            ranges={[{'text':ReactHtmlParser(props.response)}]} 
            enabled={true}
            onTextHighlighted={props.handleSelection}
            highlightStyle={{backgroundColor:'red'}}
            text={ReactHtmlParser(props.response)}
        />*/}
    </div>
);

export default PresentExp;