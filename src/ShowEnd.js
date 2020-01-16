
const ShowEnd = (props) => (
    <div>
        <h4>You're all done. Your percentage correct was: {props.score}%</h4>
        <h4>Your average rating of match between rationales was {props.avgAssess}</h4>
        <a href="http://study.globalcognition.org/course-home" className="button">Course Home</a>
    </div>
);

export default ShowEnd;