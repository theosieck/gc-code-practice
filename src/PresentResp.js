import ReactHtmlParser from 'react-html-parser';

const divStyle = {
    marginTop: '50px'
};

const PresentResp = (props) => (
    <div style={divStyle}>
        <h2>Case: {props.respId}</h2>

        <p>{ReactHtmlParser(props.response)}</p>
    </div>
);

export default PresentResp;