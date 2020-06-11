import Button from '@material-ui/core/Button';
import Row from './Row';


const genCodes = (codes,excerpts) => {
    const codeArray = [];
    for(let i=1;i<=codes.length;i++) {
        if(excerpts[i]) {
            codeArray[i] = [codes[i],excerpts[i]];
        }
    }
    return codeArray;
}

const ReviewComp = (props) => {
    let codes = genCodes(props.codes,props.excerpts)
    return (
        <div>
        <h2>Singles</h2>
        {(codes.length>0) && codes.map((code,codeNum) => (
            <Row
                code={<Button
                            variant={props.state.clicked[codeNum]==1 ? "contained" : "outlined"}
                            onClick={props.handleButton}
                            style={{display:'block'}}
                        >
                            {codeNum}. {code[0]}
                        </Button>
                }
                selection={code[1]}
            />
        ))}
        {(codes.length<=0) && <p>No singles to review.</p>}
        </div>
    )
}

export default ReviewComp;