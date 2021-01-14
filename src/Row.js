import Grid from '@material-ui/core/Grid';

const buttonStyles = {
    backgroundColor:'white',
    color:'red',
    width:'1vw',
    padding:'1px'
}

const Row = (props) => (
    <Grid container direction="row" justify="space-between" alignItems="center" style={{marginBottom:'10px'}}>
    <Grid item xs={3} zeroMinWidth>{props.showDelete && <button id={props.code} style={buttonStyles} onClick={props.handleDelete}>x</button>} {props.code}</Grid>
    <Grid item xs={9} zeroMinWidth>{props.selection}</Grid>
    </Grid>
)

export default Row;
