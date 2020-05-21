
import ReactHtmlParser from 'react-html-parser';

const divStyle = {
    marginTop: '50px'
};

const PresentResp = (props) => (
    <div style={divStyle}>
        <h2>Case: {props.respId}</h2>

        {ReactHtmlParser(props.response)}
    </div>
);

export default PresentResp;