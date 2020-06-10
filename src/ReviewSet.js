import Button from '@material-ui/core/Button';
import Row from './Row';


const genCodes = (codes,reviewSet) => {
    const codeArray = [];
    for(let i=1;i<=codes.length;i++) {
        if(reviewSet[i]) {
            codeArray[i] = [codes[i],reviewSet[i]];
        }
    }
    return codeArray;
}

const ReviewSet = (props) => (
    <div>
    <h2>Singles:</h2>
    {genCodes(props.codes,props.reviewSet).map((code,codeNum) => (
        <Row
            code={<Button
                        variant={props.state.clicked[codeNum-1]==1 ? "contained" : "outlined"}
                        onClick={props.handleButton}
                        style={{display:'block'}}
                    >
                        {codeNum}. {code[0]}
                    </Button>
            }
            selection={code[1]}
        />
    ))}
    </div>
)

export default ReviewSet;